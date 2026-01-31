<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan; // Pastikan model Penjualan sudah di-import
use App\Models\DetailPenjualan; // Pastikan model DetailPenjualan sudah di-import
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <<< TAMBAHKAN INI
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * [READ] Menampilkan daftar riwayat penjualan dengan filter dan pagination.
     * Endpoint: GET /api/riwayat-penjualan
     * Query Params: search, start_date, end_date
     */
    public function index(Request $request)
    {
        // 1. Inisialisasi Query
        $penjualans = Penjualan::with(['pelanggan', 'user:id,name'])
            ->latest();

        // 2. Implementasi Filter/Search
        if ($request->filled('search')) {
            $search = $request->search;
            $penjualans->where('kode_penjualan', 'like', "%{$search}%")
                ->orWhereHas('pelanggan', function ($q) use ($search) {
                    $q->where('nama_pelanggan', 'like', "%{$search}%");
                });
        }

        // 3. Implementasi Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Filter berdasarkan tanggal pembuatan (created_at)
            $penjualans->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        // 4. Ambil data dengan Pagination
        $data = $penjualans->paginate(15);

        return response()->json($data);
    }

    /**
     * [READ] Menampilkan detail penjualan (data invoice).
     * Endpoint: GET /api/riwayat-penjualan/{penjualan}
     */
    public function show(Penjualan $penjualan)
    {
        // 1. Load relasi yang diperlukan oleh Android Model (PenjualanDetailResponse.java)
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');

        // 2. Hitung total item (sesuai kebutuhan Android Model)
        $totalItems = $penjualan->detailPenjualans->sum('qty');

        // 3. Siapkan detail item
        // detailPenjualans sudah memiliki kolom 'diskon_percent' dan 'diskon_nominal'
        $detailItems = $penjualan->detailPenjualans->map(function ($detail) {
            // Pastikan Anda memilih semua kolom yang dibutuhkan oleh DetailPenjualan.java di Android
            return [
                'nama_produk' => $detail->produk->nama_produk, // Dipakai di adapter
                'harga_satuan' => $detail->harga_satuan,
                'qty' => $detail->qty,
                'diskon_percent' => $detail->diskon_percent, // <--- DATA DISKON ADA DI SINI
                'diskon_nominal' => $detail->diskon_nominal, // <--- DATA DISKON ADA DI SINI
                'subtotal' => $detail->subtotal,
            ];
        });


        // 4. Kembalikan respons JSON sesuai struktur PenjualanDetailResponse.java
        return response()->json([
            'penjualan_header' => $penjualan, // Akan mengembalikan objek Penjualan lengkap
            'detail_items' => $detailItems,
            'total_items' => $totalItems,
        ]);
    }

    /**
     * [CETAK] Menyediakan URL untuk mencetak/mengunduh PDF Invoice.
     * Endpoint: GET /api/riwayat-penjualan/{penjualan}/print
     */
    public function printInvoice(Penjualan $penjualan)
    {
        // 1. Load relasi
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');
        $isDiscountApplied = true;

        // 2. Hitung Total (Sama seperti printWithDiscount)
        // Asumsi: Kita bisa menggunakan $penjualan->total_harga dari database (total setelah diskon item)
        $subTotalAwal = $penjualan->total_harga;

        // Total Final adalah total_bayar dari database (setelah diskon transaksi)
        $totalFinal = $penjualan->total_bayar;

        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalFinal;

        // Tambahkan variabel item_total_type untuk mengontrol di view
        $item_total_type = 'DISKON';

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalFinal', 'bayar', 'kembalian', 'item_total_type');

        // 3. Generate PDF (Sama seperti di InvoiceController)
        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            // SetPaper dan setOptions harus sesuai dengan yang Anda inginkan
            ->setPaper([0, 0, 595, 420], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'chroot' => public_path(), 'defaultFont' => 'Courier New']);

        // 4. KEMBALIKAN PDF STREAM
        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
        // TIDAK ADA LAGI response()->json([...])
    }
}
