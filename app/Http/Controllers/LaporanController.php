<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:laporan.index')->only('indexPembelian', 'exportPDFPembelian', 'exportExcelPembelian');
    }

    /**
     * Helper untuk menentukan rentang tanggal berdasarkan preset
     */
    protected function getDateRange(string $preset, ?string $startDate, ?string $endDate): array
    {
        $now = now();

        switch ($preset) {
            case 'today':
                $start = $now->startOfDay()->toDateString();
                $end   = $now->endOfDay()->toDateString();
                break;
            case 'this_week':
                $start = $now->startOfWeek()->toDateString();
                $end   = $now->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $start = $now->startOfMonth()->toDateString();
                $end   = $now->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $start = $now->startOfYear()->toDateString();
                $end   = $now->endOfYear()->toDateString();
                break;
            case 'custom':
                // Pastikan custom range valid
                $start = $startDate ?: $now->startOfDay()->toDateString();
                $end   = $endDate ?: $now->endOfDay()->toDateString();
                break;
            default: // all (default) atau jika preset tidak dikenal
                // Tampilkan semua data (rentang luas, misalnya 10 tahun terakhir)
                $start = Carbon::now()->subYears(10)->toDateString();
                $end   = $now->endOfDay()->toDateString();
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Menampilkan halaman laporan pembelian dengan filter tanggal dan status.
     */
    public function indexPembelian(Request $request)
    {
        $preset    = $request->input('preset', 'all'); // New preset filter, default 'all'
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        // Dapatkan rentang tanggal
        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        // Tentukan label status
        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // Query Utama dengan Relasi 'pemasok' (bukan 'supplier')
        $pembelians = Pembelian::with(['returPembelians', 'pemasok']) // Load relasi pemasok
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->orderBy('tanggal_pembelian', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Query untuk Total Bayar Keseluruhan (tanpa pagination)
        // KODE DIPERBAIKI: Harus ambil data dan menggunakan accessor 'sisa_total_bayar'
        $totalBayarNettoCollection = Pembelian::with('returPembelians') // Load relasi retur untuk accessor
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->get();

        $total_bayar_all = $totalBayarNettoCollection->sum('sisa_total_bayar'); // <-- MENGGUNAKAN NETTO

        return view('pages.laporan.pembelian.index', [
            'pembelians' => $pembelians,
            'total_bayar_all' => $total_bayar_all,
            'status_label' => $statusLabel,
            // Kirim tanggal yang sudah difilter kembali ke view
            'start_date_filtered' => $startDate,
            'end_date_filtered' => $endDate,
        ]);
    }

    /**
     * Export laporan pembelian ke format PDF.
     */
    public function exportPDFPembelian(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y');

        $statusLabel = match ($status) {
            'completed' => 'Completed (Tidak Ada Retur)',
            'return' => 'Retur (Sebagian/Penuh)',
            default => 'Semua Status',
        };

        // --- Gunakan chunk untuk load data besar secara bertahap ---
        $pembelians = collect(); // tampung hasil dari tiap chunk

        Pembelian::with(['returPembelians', 'pemasok'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->orderBy('tanggal_pembelian', 'asc')
            ->chunk(500, function ($rows) use (&$pembelians) {
                foreach ($rows as $row) {
                    $pembelians->push($row);
                }
            });
        // --- end chunk ---

        // KODE DIPERBAIKI: Summing 'sisa_total_bayar' (Netto)
        $total_bayar_all = $pembelians->sum('sisa_total_bayar'); // <-- MENGGUNAKAN NETTO
        $filename = 'Laporan_Pembelian_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.pdf';

        $pdf = Pdf::setPaper('a4', 'portrait');

        return $pdf->loadView('pages.laporan.pembelian.pdf', [
            'pembelians' => $pembelians,
            'periode' => $periode,
            'total_bayar_all' => $total_bayar_all,
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
    }


    /**
     * Export laporan pembelian ke format Excel.
     */
    public function exportExcelPembelian(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y');

        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- HEADER ---
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(4)->setRowHeight(5);

        // --- HEADER KOLOM ---
        $headers = ['No', 'Tanggal', 'Kode Transaksi', 'Pemasok', 'Total Bayar', 'Status'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB8CCE4');
        $sheet->getStyle('A5:F5')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // --- ISI DATA ---
        $row = 6;
        $index = 1;
        $total_bayar_all = 0; // Inisialisasi total Netto

        Pembelian::with(['returPembelians', 'pemasok'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->orderBy('tanggal_pembelian', 'asc')
            ->chunk(500, function ($rows) use (&$sheet, &$row, &$index, &$total_bayar_all) {
                foreach ($rows as $pembelian) {

                    // KODE DIPERBAIKI: Menggunakan accessor sisa_total_bayar (Netto)
                    $bayarNetto = $pembelian->sisa_total_bayar;

                    $sheet->setCellValue('A' . $row, $index++);
                    $sheet->setCellValue('B' . $row, Carbon::parse($pembelian->tanggal_pembelian)->format('d/m/Y'));
                    $sheet->setCellValue('C' . $row, $pembelian->kode_transaksi);
                    $sheet->setCellValue('D' . $row, $pembelian->pemasok->nama ?? '-');
                    $sheet->setCellValue('E' . $row, $bayarNetto); // <-- MENAMPILKAN NETTO

                    // Logika Status
                    if ($pembelian->total_nilai_retur >= $pembelian->total_bayar) {
                        $status_display = 'Retur Penuh';
                    } elseif ($pembelian->total_nilai_retur > 0) {
                        $status_display = 'Retur Sebagian';
                    } else {
                        $status_display = 'Completed';
                    }
                    $sheet->setCellValue('F' . $row, $status_display);

                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('F' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    // KODE DIPERBAIKI: Mengakumulasi 'sisa_total_bayar' (Netto)
                    $total_bayar_all += $bayarNetto;
                    $row++;
                }
            });

        // --- TOTAL KESELURUHAN ---
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN:');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $total_bayar_all); // <-- MENAMPILKAN TOTAL NETTO
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\"Rp\"#,##0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E3F2');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // --- AUTO SIZE KOLOM ---
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Pembelian_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
    }


    public function indexPenjualan(Request $request)
    {
        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        // Dapatkan rentang tanggal
        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        // Tentukan label status
        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // Query Utama dengan Relasi 'pelanggan' dan 'returPenjualans'
        $penjualans = Penjualan::with(['returPenjualans', 'pelanggan']) // Load relasi pelanggan
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    // Penjualan yang tidak memiliki retur
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    // Penjualan yang memiliki retur
                    return $query->has('returPenjualans');
                }
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Query untuk Total Bayar Keseluruhan (tanpa pagination)
        $total_bayar_all = Penjualan::whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    return $query->has('returPenjualans');
                }
            })
            ->sum('total_bayar');

        return view('pages.laporan.penjualan.index', [ // <-- UBAH PATH VIEW
            'penjualans' => $penjualans, // <-- UBAH NAMA VARIABEL
            'total_bayar_all' => $total_bayar_all,
            'status_label' => $statusLabel,
            'start_date_filtered' => $startDate,
            'end_date_filtered' => $endDate,
        ]);
    }

    /**
     * Export laporan penjualan ke format PDF.
     */
    public function exportPDFPenjualan(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y');
        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // --- Load data secara bertahap (chunking) ---
        $penjualans = collect();

        Penjualan::with(['pelanggan', 'returPenjualans'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    return $query->has('returPenjualans');
                }
            })
            ->orderBy('tanggal_penjualan', 'asc')
            ->chunk(500, function ($rows) use ($penjualans) {
                foreach ($rows as $row) {
                    $penjualans->push($row);
                }
            });

        $total_bayar_all = $penjualans->sum('total_bayar');
        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.pdf';

        $pdf = Pdf::setPaper('a4', 'portrait');

        return $pdf->loadView('pages.laporan.penjualan.pdf', [
            'penjualans' => $penjualans,
            'periode' => $periode,
            'total_bayar_all' => $total_bayar_all,
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
    }




    /**
     * Export laporan penjualan ke format Excel.
     */
    public function exportExcelPenjualan(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y');
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
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(4)->setRowHeight(5);

        // --- HEADER KOLOM ---
        $headers = ['No', 'Tanggal', 'Kode Transaksi', 'Pelanggan', 'Total Bayar', 'Status'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB8CCE4');
        $sheet->getStyle('A5:F5')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // --- ISI DATA ---
        $row = 6;
        $index = 1;
        $total_bayar_all = 0;

        Penjualan::with(['pelanggan', 'returPenjualans'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
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
                    $sheet->setCellValue('E' . $row, $penjualan->total_bayar);

                    $status_display = $penjualan->returPenjualans->isNotEmpty() ? 'Retur' : 'Completed';
                    $sheet->setCellValue('F' . $row, $status_display);

                    // Styling baris
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('F' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $total_bayar_all += $penjualan->total_bayar;
                    $row++;
                }
            });

        // --- TOTAL ---
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN:');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $total_bayar_all);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\"Rp\"#,##0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E3F2');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
    }
}
