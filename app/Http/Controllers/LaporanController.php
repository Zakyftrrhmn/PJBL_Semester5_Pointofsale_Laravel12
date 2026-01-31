<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\Pages;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Produk;
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
            ->orderBy('created_at', 'desc')
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
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
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
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
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

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // Tambahkan eager loading 'detailPenjualans.produk' dan 'user'
        $penjualans = Penjualan::with(['returPenjualans', 'pelanggan', 'user', 'detailPenjualans.produk'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') return $query->doesntHave('returPenjualans');
                if ($status === 'return') return $query->has('returPenjualans');
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Hitung ringkasan total untuk dashboard laporan
        $summary = Penjualan::whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') return $query->doesntHave('returPenjualans');
                if ($status === 'return') return $query->has('returPenjualans');
            })
            ->get();

        $total_bayar_all = $summary->sum('total_bayar');
        $total_modal_all = $summary->sum('total_modal'); // Memanggil accessor di model
        $total_laba_all  = $summary->sum('laba');        // Memanggil accessor di model

        return view('pages.laporan.penjualan.index', [
            'penjualans' => $penjualans,
            'total_bayar_all' => $total_bayar_all,
            'total_modal_all' => $total_modal_all,
            'total_laba_all' => $total_laba_all,
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
        // Meningkatkan limit memori dan waktu eksekusi untuk pengolahan data besar & PDF
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');

        // Ambil rentang tanggal dari helper internal
        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        $periode = \Carbon\Carbon::parse($startDate)->format('d F Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d F Y');

        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        // Load data dengan relasi lengkap untuk menghindari N+1 Query
        // Kita butuh detailPenjualans.produk untuk menghitung 'total_modal'
        $penjualans = Penjualan::with(['pelanggan', 'user', 'returPenjualans', 'detailPenjualans.produk'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') return $query->doesntHave('returPenjualans');
                if ($status === 'return') return $query->has('returPenjualans');
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung Ringkasan untuk Footer Laporan
        $total_bayar_all = $penjualans->sum('total_bayar');
        $total_modal_all = $penjualans->sum('total_modal'); // Memanggil accessor getTotalModalAttribute
        $total_laba_all  = $penjualans->sum('laba');        // Memanggil accessor getLabaAttribute

        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', '(', ')'], '_', $periode) . '.pdf';

        // Gunakan Landscape karena kolom sangat banyak (10 kolom)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setPaper('a4', 'landscape');

        return $pdf->loadView('pages.laporan.penjualan.pdf', [
            'penjualans'      => $penjualans,
            'periode'         => $periode,
            'total_bayar_all' => $total_bayar_all,
            'total_modal_all' => $total_modal_all,
            'total_laba_all'  => $total_laba_all,
            'status_label'    => $statusLabel,
            'preset_label'    => match ($preset) {
                'today'      => 'Hari Ini',
                'this_week'  => 'Minggu Ini',
                'this_month' => 'Bulan Ini',
                'this_year'  => 'Tahun Ini',
                'custom'     => 'Custom Range',
                default      => 'Seluruhnya',
            },
        ])->stream($filename);
    }

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

        $periode = \Carbon\Carbon::parse($startDate)->format('d F Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d F Y');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // HEADER LAPORAN
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LAPORAN DETAIL PENJUALAN, MODAL & LABA');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // HEADER KOLOM (Sesuai permintaan Dosen)
        $headers = ['NO', 'NO. INVOICE', 'TANGGAL', 'PELANGGAN', 'KASIR', 'SUB TOTAL', 'DISKON (%)', 'TOTAL BAYAR', 'MODAL', 'LABA'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:J5')->getFont()->setBold(true);
        $sheet->getStyle('A5:J5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:J5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB8CCE4');

        $row = 6;
        $index = 1;
        $grand_total_bayar = 0;
        $grand_total_modal = 0;
        $grand_total_laba  = 0;

        Penjualan::with(['pelanggan', 'user', 'detailPenjualans.produk', 'returPenjualans'])
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') return $query->doesntHave('returPenjualans');
                if ($status === 'return') return $query->has('returPenjualans');
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->chunk(500, function ($rows) use (&$sheet, &$row, &$index, &$grand_total_bayar, &$grand_total_modal, &$grand_total_laba) {
                foreach ($rows as $p) {
                    $sheet->setCellValue('A' . $row, $index++);
                    $sheet->setCellValue('B' . $row, $p->kode_penjualan);
                    $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d/m/Y'));
                    $sheet->setCellValue('D' . $row, $p->pelanggan->nama_pelanggan ?? 'Umum');
                    $sheet->setCellValue('E' . $row, $p->user->name ?? '-');

                    // DATA KEUANGAN
                    $sheet->setCellValue('F' . $row, $p->total_harga);     // Subtotal sebelum diskon final
                    $sheet->setCellValue('G' . $row, $p->diskon_percent);  // Diskon (%)
                    $sheet->setCellValue('H' . $row, $p->total_bayar);     // Net Bayar
                    $sheet->setCellValue('I' . $row, $p->total_modal);     // Modal (dari Accessor)
                    $sheet->setCellValue('J' . $row, $p->laba);            // Laba (dari Accessor)

                    // Formatting Ribuan (IDR)
                    $sheet->getStyle('F' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0');

                    $grand_total_bayar += $p->total_bayar;
                    $grand_total_modal += $p->total_modal;
                    $grand_total_laba  += $p->laba;
                    $row++;
                }
            });

        // FOOTER TOTAL
        $sheet->mergeCells("A$row:G$row");
        $sheet->setCellValue("A$row", "GRAND TOTAL:");
        $sheet->setCellValue("H$row", $grand_total_bayar);
        $sheet->setCellValue("I$row", $grand_total_modal);
        $sheet->setCellValue("J$row", $grand_total_laba);
        $sheet->getStyle("A$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("H$row:J$row")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'J') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Penjualan_Lengkap_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Laporan Penjualan Per Produk dengan Filter Kode Unit (Prefix)
     */
    public function indexPenjualanPerProduk(Request $request)
    {
        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');
        $prefix    = $request->input('prefix', 'all');
        $kategoriId = $request->input('kategori_id', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];

        // ===== PERBAIKAN: Ambil prefix dengan regex (huruf di awal) =====
        $prefixes = Produk::selectRaw("REGEXP_REPLACE(kode_produk, '[0-9].*', '') as prefix")
            ->distinct()
            ->pluck('prefix')
            ->filter()
            ->sort()
            ->values();
        // ===== AKHIR PERBAIKAN =====

        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();

        $query = DetailPenjualan::with(['penjualan.pelanggan', 'penjualan.user', 'produk.kategori'])
            ->whereHas('penjualan', function ($q) use ($startDate, $endDate, $status) {
                $q->whereBetween('tanggal_penjualan', [$startDate, $endDate]);
                if ($status !== 'all') {
                    if ($status === 'completed') {
                        $q->doesntHave('returPenjualans');
                    } elseif ($status === 'return') {
                        $q->has('returPenjualans');
                    }
                }
            });

        if ($prefix !== 'all') {
            $query->whereHas('produk', function ($q) use ($prefix) {
                $q->where('kode_produk', 'LIKE', $prefix . '%');
            });
        }

        if ($kategoriId !== 'all') {
            $query->whereHas('produk', function ($q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            });
        }

        $detailPenjualans = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $summary = DetailPenjualan::whereHas('penjualan', function ($q) use ($startDate, $endDate, $status) {
            $q->whereBetween('tanggal_penjualan', [$startDate, $endDate]);
            if ($status === 'completed') $q->doesntHave('returPenjualans');
            if ($status === 'return') $q->has('returPenjualans');
        })
            ->when($prefix !== 'all', function ($q) use ($prefix) {
                $q->whereHas('produk', fn($qq) => $qq->where('kode_produk', 'LIKE', $prefix . '%'));
            })
            ->when($kategoriId !== 'all', function ($q) use ($kategoriId) {
                $q->whereHas('produk', fn($qq) => $qq->where('kategori_id', $kategoriId));
            })
            ->get();

        $total_qty = $summary->sum('qty');
        $total_subtotal = $summary->sum('subtotal');
        $total_modal = $summary->sum(function ($detail) {
            return $detail->qty * ($detail->produk->harga_beli ?? 0);
        });
        $total_laba = $total_subtotal - $total_modal;

        return view('pages.laporan.penjualan-per-produk.index', compact(
            'detailPenjualans',
            'prefixes',
            'kategoris',
            'total_qty',
            'total_subtotal',
            'total_modal',
            'total_laba',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export PDF Laporan Penjualan Per Produk
     */
    public function exportPDFPenjualanPerProduk(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');
        $prefix    = $request->input('prefix', 'all');
        $kategoriId = $request->input('kategori_id', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];
        $periode = \Carbon\Carbon::parse($startDate)->format('d F Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d F Y');

        $page = \App\Models\Pages::first();

        $query = DetailPenjualan::with(['penjualan.pelanggan', 'produk.kategori'])
            ->whereHas('penjualan', function ($q) use ($startDate, $endDate, $status) {
                $q->whereBetween('tanggal_penjualan', [$startDate, $endDate]);
                if ($status === 'completed') $q->doesntHave('returPenjualans');
                if ($status === 'return') $q->has('returPenjualans');
            });

        if ($prefix !== 'all') {
            $query->whereHas('produk', fn($q) => $q->where('kode_produk', 'LIKE', $prefix . '%'));
        }

        if ($kategoriId !== 'all') {
            $query->whereHas('produk', fn($q) => $q->where('kategori_id', $kategoriId));
        }

        $detailPenjualans = $query->get();

        $total_qty = $detailPenjualans->sum('qty');
        $total_subtotal = $detailPenjualans->sum('subtotal');
        $total_modal = $detailPenjualans->sum(fn($d) => $d->qty * ($d->produk->harga_beli ?? 0));
        $total_laba = $total_subtotal - $total_modal;

        $statusLabel = match ($status) {
            'completed' => 'Completed',
            'return' => 'Retur',
            default => 'Semua Status',
        };

        $prefixLabel = $prefix === 'all' ? 'Semua Kode Unit' : 'Kode Unit: ' . $prefix;
        $kategoriLabel = $kategoriId === 'all' ? 'Semua Kategori' : 'Kategori: ' . \App\Models\Kategori::find($kategoriId)->nama_kategori ?? '-';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setPaper('a4', 'landscape');

        return $pdf->loadView('pages.laporan.penjualan-per-produk.pdf', compact(
            'detailPenjualans',
            'periode',
            'total_qty',
            'total_subtotal',
            'total_modal',
            'total_laba',
            'statusLabel',
            'prefixLabel',
            'kategoriLabel',
            'page'
        ))->stream('Laporan_Penjualan_Per_Produk_' . date('YmdHis') . '.pdf');
    }

    /**
     * Export Excel Laporan Penjualan Per Produk
     */
    public function exportExcelPenjualanPerProduk(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $preset    = $request->input('preset', 'all');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', 'all');
        $prefix    = $request->input('prefix', 'all');
        $kategoriId = $request->input('kategori_id', 'all');

        $dateRange = $this->getDateRange($preset, $startDate, $endDate);
        $startDate = $dateRange['start'];
        $endDate   = $dateRange['end'];
        $periode = \Carbon\Carbon::parse($startDate)->format('d F Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d F Y');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN PER PRODUK');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['NO', 'TANGGAL', 'INVOICE', 'KODE PRODUK', 'NAMA PRODUK', 'KATEGORI', 'QTY', 'HARGA', 'SUBTOTAL', 'MODAL', 'LABA'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:K5')->getFont()->setBold(true);
        $sheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB8CCE4');

        $row = 6;
        $index = 1;
        $grand_qty = $grand_subtotal = $grand_modal = $grand_laba = 0;

        DetailPenjualan::with(['penjualan', 'produk'])
            ->whereHas('penjualan', function ($q) use ($startDate, $endDate, $status) {
                $q->whereBetween('tanggal_penjualan', [$startDate, $endDate]);
                if ($status === 'completed') $q->doesntHave('returPenjualans');
                if ($status === 'return') $q->has('returPenjualans');
            })
            ->when($prefix !== 'all', fn($q) => $q->whereHas('produk', fn($qq) => $qq->where('kode_produk', 'LIKE', $prefix . '%')))
            ->when($kategoriId !== 'all', fn($q) => $q->whereHas('produk', fn($qq) => $qq->where('kategori_id', $kategoriId)))
            ->chunk(500, function ($rows) use (&$sheet, &$row, &$index, &$grand_qty, &$grand_subtotal, &$grand_modal, &$grand_laba) {
                foreach ($rows as $d) {
                    $modal = $d->qty * ($d->produk->harga_beli ?? 0);
                    $laba = $d->subtotal - $modal;

                    $sheet->setCellValue('A' . $row, $index++);
                    $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($d->penjualan->tanggal_penjualan)->format('d/m/Y'));
                    $sheet->setCellValue('C' . $row, $d->penjualan->kode_penjualan);
                    $sheet->setCellValue('D' . $row, $d->produk->kode_produk ?? '-');
                    $sheet->setCellValue('E' . $row, $d->produk->nama_produk ?? '-');
                    $sheet->setCellValue('F' . $row, $d->produk->kategori->nama_kategori ?? '-');
                    $sheet->setCellValue('G' . $row, $d->qty);
                    $sheet->setCellValue('H' . $row, $d->harga_satuan);
                    $sheet->setCellValue('I' . $row, $d->subtotal);
                    $sheet->setCellValue('J' . $row, $modal);
                    $sheet->setCellValue('K' . $row, $laba);

                    $sheet->getStyle('H' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('#,##0');

                    $grand_qty += $d->qty;
                    $grand_subtotal += $d->subtotal;
                    $grand_modal += $modal;
                    $grand_laba += $laba;
                    $row++;
                }
            });

        $sheet->mergeCells("A$row:F$row");
        $sheet->setCellValue("A$row", "GRAND TOTAL:");
        $sheet->setCellValue("G$row", $grand_qty);
        $sheet->setCellValue("I$row", $grand_subtotal);
        $sheet->setCellValue("J$row", $grand_modal);
        $sheet->setCellValue("K$row", $grand_laba);
        $sheet->getStyle("A$row:K$row")->getFont()->setBold(true);

        foreach (range('A', 'K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan_Penjualan_Per_Produk_' . date('YmdHis') . '.xlsx"');
        $writer->save('php://output');
        exit;
    }
}
