<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan; // Pastikan model Penjualan sudah di-import
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
// Import untuk Excel Export
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanController extends Controller
{
    /**
     * Helper untuk menentukan rentang tanggal berdasarkan preset atau tanggal kustom
     */
    protected function getDateRange(string $preset, ?string $startDate, ?string $endDate): array
    {
        $now = now();
        $start = null;
        $end = null;

        switch ($preset) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'this_week':
                // Gunakan start/endOfWeek() tanpa parameter untuk default Monday/Sunday
                $start = $now->copy()->startOfWeek();
                $end   = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $start = Carbon::parse($startDate)->startOfDay();
                    $end   = Carbon::parse($endDate)->endOfDay();
                }
                break;
            default: // Default: Bulan Ini (atau bisa diubah ke All Time jika perlu)
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
        }

        // Untuk preset 'all' di Web Controller, rentangnya sangat luas. 
        // Untuk API, lebih aman menggunakan rentang default (misal: bulan ini) atau 'all time' yang sangat luas.
        if ($preset === 'all') {
            $start = Carbon::now()->subYears(10)->startOfDay();
            $end   = $now->copy()->endOfDay();
        }

        return [$start, $end];
    }

    /**
     * [READ] Menampilkan laporan penjualan berdasarkan filter tanggal dan status.
     * Endpoint: GET /api/laporan/penjualan?preset=...&start_date=...&end_date=...&status=...
     */
    public function indexPenjualan(Request $request)
    {
        // 1. Tentukan Rentang Tanggal & Status
        $preset = $request->get('preset', 'this_month');
        $startDateParam = $request->get('start_date');
        $endDateParam = $request->get('end_date');
        $status = $request->get('status', 'all'); // Ambil filter status

        [$startDate, $endDate] = $this->getDateRange($preset, $startDateParam, $endDateParam);

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Filter tanggal tidak valid.'], 400);
        }

        try {
            // Base Query untuk Penjualan (PAGINATED & SUMMARY)
            $baseQuery = Penjualan::with(['pelanggan:id,nama_pelanggan', 'returPenjualans'])
                ->whereBetween('tanggal_penjualan', [$startDate, $endDate]) // <<< KOREKSI: Menggunakan tanggal_penjualan
                ->when($status !== 'all', function ($query) use ($status) {
                    if ($status === 'completed') {
                        return $query->doesntHave('returPenjualans');
                    } elseif ($status === 'return') {
                        return $query->has('returPenjualans');
                    }
                });

            // 2. Query Data Penjualan (Paginated)
            $penjualans = $baseQuery->clone()
                ->latest('tanggal_penjualan') // Urutkan berdasarkan tanggal_penjualan
                ->paginate(20)
                ->withQueryString();

            // 3. Hitung Ringkasan Total (untuk semua data yang difilter)
            $totalQuery = $baseQuery->clone();

            // Asumsi: total_harga = Kotor (Gross), diskon_nominal = Diskon, total_bayar = Bersih (Netto)
            // KOREKSI: Perbaiki logika perhitungan summary
            $totalPenjualanKotor = (float) $totalQuery->sum('total_harga');
            $totalDiskonTransaksi = (float) $totalQuery->sum('diskon_nominal');
            $totalPenjualanBersih = (float) $totalQuery->sum('total_bayar'); // Ini adalah total Netto

            // 4. Format Output
            return response()->json([
                'filter_info' => [
                    'preset' => $preset,
                    'status' => $status, // Kirim status filter
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'summary' => [
                    'total_transaksi' => $penjualans->total(),
                    'penjualan_kotor' => $totalPenjualanKotor,
                    'diskon_transaksi' => $totalDiskonTransaksi,
                    'penjualan_bersih' => $totalPenjualanBersih,
                    'total_laba' => 0.0, // Tetap 0.0 jika laba tidak dihitung di sini
                ],
                'data' => $penjualans,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Kesalahan database saat memuat laporan. Cek nama kolom dan relasi.',
                'error_detail' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kesalahan server internal yang tidak terduga.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export laporan penjualan ke format PDF (API).
     * Endpoint: GET /api/laporan/penjualan/pdf?preset=...&start_date=...&end_date=...&status=...
     */
    public function exportPDFPenjualan(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        // 1. Dapatkan Rentang Tanggal dan Filter
        $preset = $request->get('preset', 'this_month');
        $startDateParam = $request->get('start_date');
        $endDateParam = $request->get('end_date');
        $status = $request->get('status', 'all');

        [$startDate, $endDate] = $this->getDateRange($preset, $startDateParam, $endDateParam);

        if (!$startDate || !$endDate) {
            // Untuk API, lebih baik kembalikan JSON error
            return response()->json(['message' => 'Filter tanggal tidak valid.'], 400);
        }

        try {
            $periode = $startDate->format('d F Y') . ' s/d ' . $endDate->format('d F Y');

            $statusLabel = match ($status) {
                'completed' => 'Completed (Tidak Ada Retur)',
                'return' => 'Retur (Sebagian/Penuh)',
                default => 'Semua Status',
            };

            // 2. Ambil Semua Data Penjualan (TANPA PAGINATION)
            $penjualans = collect();

            $baseQuery = Penjualan::with(['pelanggan', 'returPenjualans'])
                ->whereBetween('tanggal_penjualan', [$startDate, $endDate]) // <<< KOREKSI: Menggunakan tanggal_penjualan
                ->when($status !== 'all', function ($query) use ($status) {
                    if ($status === 'completed') {
                        return $query->doesntHave('returPenjualans');
                    } elseif ($status === 'return') {
                        return $query->has('returPenjualans');
                    }
                })
                ->orderBy('tanggal_penjualan', 'asc');

            // Gunakan chunk untuk data besar
            $baseQuery->chunk(500, function ($rows) use (&$penjualans) {
                foreach ($rows as $row) {
                    $penjualans->push($row);
                }
            });

            // 3. Hitung Ringkasan Total
            // Asumsi: total_harga = Kotor (Gross), diskon_nominal = Diskon, total_bayar = Bersih (Netto)
            $totalPenjualanKotor = (float) $penjualans->sum('total_harga');
            $totalDiskonTransaksi = (float) $penjualans->sum('diskon_nominal');
            $totalPenjualanBersih = (float) $penjualans->sum('total_bayar'); // Total Bersih/Netto

            // 4. Siapkan Data untuk PDF
            $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.pdf';

            $pdf = Pdf::setPaper('a4', 'portrait');

            return $pdf->loadView('pages.laporan.penjualan.pdf', [
                'penjualans' => $penjualans,
                'periode' => $periode,
                'total_bayar_all' => $totalPenjualanBersih, // Mengirim total bersih
                'total_penjualan_kotor' => $totalPenjualanKotor,
                'total_diskon_transaksi' => $totalDiskonTransaksi,
                'status_label' => $statusLabel,
                'preset_label' => match ($preset) {
                    'today' => 'Hari Ini',
                    'this_week' => 'Minggu Ini',
                    'this_month' => 'Bulan Ini',
                    'this_year' => 'Tahun Ini',
                    'custom' => 'Custom Range',
                    default => 'Seluruhnya',
                },
            ])->stream($filename);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat file PDF laporan penjualan.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Export laporan penjualan ke format Excel (API).
     * Endpoint: GET /api/laporan/penjualan/excel?preset=...&start_date=...&end_date=...&status=...
     */
    public function exportExcelPenjualan(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset     = $request->input('preset', 'this_month');
        $startDateParam = $request->input('start_date');
        $endDateParam = $request->input('end_date');
        $status     = $request->input('status', 'all');

        [$startDate, $endDate] = $this->getDateRange($preset, $startDateParam, $endDateParam);

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Filter tanggal tidak valid.'], 400);
        }

        $periode = $startDate->format('d F Y') . ' s/d ' . $endDate->format('d F Y');
        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- HEADER UTAMA ---
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(4)->setRowHeight(5);

        // --- HEADER KOLOM ---
        $headers = ['No', 'Tanggal', 'Kode Transaksi', 'Pelanggan', 'Total Bayar (Netto)', 'Status'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB8CCE4');
        $sheet->getStyle('A5:F5')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // --- ISI DATA ---
        $row = 6;
        $index = 1;
        $total_bayar_all = 0; // Total Bersih/Netto

        Penjualan::with(['pelanggan', 'returPenjualans'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate]) // <<< KOREKSI: Menggunakan tanggal_penjualan
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    return $query->has('returPenjualans');
                }
            })
            ->orderBy('tanggal_penjualan', 'asc')
            ->chunk(500, function ($rows) use (&$sheet, &$row, &$index, &$total_bayar_all) {
                foreach ($rows as $penjualan) {
                    $sheet->setCellValue('A' . $row, $index++);
                    $sheet->setCellValue('B' . $row, Carbon::parse($penjualan->tanggal_penjualan)->format('d/m/Y'));
                    $sheet->setCellValue('C' . $row, $penjualan->kode_transaksi);
                    $sheet->setCellValue('D' . $row, $penjualan->pelanggan->nama ?? '-');
                    $sheet->setCellValue('E' . $row, $penjualan->total_bayar); // Kolom E berisi Total Bayar (Netto)

                    // Logika Status dari Web Controller
                    $status_display = $penjualan->returPenjualans->isNotEmpty() ? 'Retur' : 'Completed';
                    $sheet->setCellValue('F' . $row, $status_display);

                    // Styling baris
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E' . $row)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('F' . $row)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $total_bayar_all += $penjualan->total_bayar;
                    $row++;
                }
            });

        // --- TOTAL ---
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN (NETTO):');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $total_bayar_all);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\"Rp\"#,##0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E3F2');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.xlsx';

        // Mengirimkan file Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
    }
}
