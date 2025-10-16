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
        $total_bayar_all = Pembelian::whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->sum('total_bayar');

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
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // Load relasi 'pemasok'
        $pembelians = Pembelian::with(['returPembelians', 'pemasok'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->orderBy('tanggal_pembelian', 'asc')
            ->get();

        $total_bayar_all = $pembelians->sum('total_bayar');
        $filename = 'Laporan_Pembelian_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.pdf';

        // --- PENGATURAN KERTAS A4 PORTRAIT (TEGAK) ---
        $pdf = Pdf::setPaper('a4', 'portrait'); // <-- DIUBAH DARI 'landscape' KE 'portrait'
        // ----------------------------------------

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

        // Load relasi 'pemasok'
        $pembelians = Pembelian::with(['returPembelians', 'pemasok'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPembelians');
                } elseif ($status === 'return') {
                    return $query->has('returPembelians');
                }
            })
            ->orderBy('tanggal_pembelian', 'asc')
            ->get();

        $total_bayar_all = $pembelians->sum('total_bayar');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul Laporan
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Informasi Periode dan Status
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(4)->setRowHeight(5); // Baris kosong pemisah

        // Header Tabel
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Tanggal');
        $sheet->setCellValue('C5', 'Kode Transaksi');
        $sheet->setCellValue('D5', 'Pemasok'); // Ganti Supplier menjadi Pemasok
        $sheet->setCellValue('E5', 'Total Bayar');
        $sheet->setCellValue('F5', 'Status');

        // Style Header
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB8CCE4');
        $sheet->getStyle('A5:F5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Isi Tabel
        $row = 6;
        foreach ($pembelians as $index => $pembelian) {
            $sheet->setCellValue('A' . $row, $index + 1);
            // Perhatikan: Model Pembelian memiliki kolom 'tanggal_pembelian'
            $sheet->setCellValue('B' . $row, Carbon::parse($pembelian->tanggal_pembelian)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $pembelian->kode_transaksi);
            // GUNAKAN relasi 'pemasok' dan null coalescing untuk menghindari error
            $sheet->setCellValue('D' . $row, $pembelian->pemasok->nama ?? '-');
            $sheet->setCellValue('E' . $row, $pembelian->total_bayar);

            // Kolom Status
            $status_display = $pembelian->returPembelians->isNotEmpty() ? 'Retur' : 'Completed';
            $sheet->setCellValue('F' . $row, $status_display);

            // Style untuk sel data
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            ]);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN:');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $total_bayar_all);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\"Rp\"#,##0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E3F2');

        // Border total
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Auto size columns
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

        // Ambil rentang tanggal
        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y');
        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        $penjualans = Penjualan::with(['returPenjualans', 'pelanggan'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') return $query->doesntHave('returPenjualans');
                if ($status === 'return') return $query->has('returPenjualans');
            })
            ->orderBy('tanggal_penjualan', 'asc')
            ->get();

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

        // Query untuk data Penjualan
        $penjualans = Penjualan::with(['returPenjualans', 'pelanggan']) // Load relasi pelanggan
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    return $query->has('returPenjualans');
                }
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();

        // Query untuk Total Bayar Keseluruhan
        $total_bayar_all = Penjualan::whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    return $query->doesntHave('returPenjualans');
                } elseif ($status === 'return') {
                    return $query->has('returPenjualans');
                }
            })
            ->sum('total_bayar');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul Laporan
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN'); // <-- UBAH JUDUL
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Periode
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Status
        $sheet->setCellValue('A3', 'Status: ' . $statusLabel);
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel
        $headers = ['No', 'Kode Penjualan', 'Tanggal', 'Pelanggan', 'Total Bayar', 'Status']; // <-- UBAH HEADER
        $sheet->fromArray($headers, NULL, 'A5');
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E3F2');
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Konten Tabel
        $row = 6;
        foreach ($penjualans as $index => $penjualan) {
            $statusData = $penjualan->returPenjualans->isNotEmpty() ? 'Retur' : 'Completed';
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $penjualan->kode_penjualan);
            $sheet->setCellValue('C' . $row, Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y'));
            $sheet->setCellValue('D' . $row, $penjualan->pelanggan->nama_pelanggan ?? '-'); // <-- UBAH KE pelanggan
            $sheet->setCellValue('E' . $row, $penjualan->total_bayar);
            $sheet->setCellValue('F' . $row, $statusData);

            // Format kolom Total Bayar sebagai mata uang
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');

            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN:');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('E' . $row, $total_bayar_all);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E3F2');

        // Border total
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Border untuk header dan data
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A5:F' . ($row - 1))->applyFromArray($styleArray);


        // Auto size columns
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.xlsx'; // <-- UBAH NAMA FILE

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
