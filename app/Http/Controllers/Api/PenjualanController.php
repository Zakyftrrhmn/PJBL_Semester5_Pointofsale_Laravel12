<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan; // Pastikan model Penjualan sudah di-import
use App\Models\DetailPenjualan; // Pastikan model DetailPenjualan sudah di-import
use Illuminate\Http\Request;
use Carbon\Carbon;
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
        // Load relasi detail penjualan, produk, dan user terkait
        $penjualan->load([
            'pelanggan',
            'user:id,name',
            'detailPenjualans.produk:id,kode_produk,nama_produk'
        ]);

        // Format data detail penjualan untuk mobile
        $detailItems = $penjualan->detailPenjualans->map(function ($detail) {
            return [
                'produk' => $detail->produk->nama_produk ?? 'Produk Dihapus',
                'kode_produk' => $detail->produk->kode_produk ?? '-',
                'qty' => (int) $detail->qty,
                'harga_satuan' => (float) $detail->harga_satuan,
                'diskon_percent' => (float) $detail->diskon_percent,
                'diskon_nominal' => (float) $detail->diskon_nominal,
                'subtotal' => (float) $detail->subtotal,
            ];
        });

        // Data lengkap untuk merender invoice di mobile
        $invoiceData = [
            'penjualan_header' => $penjualan,
            'detail_items' => $detailItems,
            'total_items' => count($detailItems),
        ];

        return response()->json($invoiceData);
    }

    /**
     * [CETAK] Menyediakan URL untuk mencetak/mengunduh PDF Invoice.
     * Endpoint: GET /api/riwayat-penjualan/{penjualan}/print
     */
    public function printInvoice(Penjualan $penjualan)
    {
        // ASUMSI: Anda sudah memiliki route web yang menghasilkan PDF invoice di /invoice/print/{id}
        // Jika belum, Anda harus membuatnya di Web Controller Anda
        $printUrl = url('/invoice/print/' . $penjualan->id);

        return response()->json([
            'message' => 'Gunakan URL ini untuk mencetak/mengunduh PDF Invoice',
            'invoice_id' => $penjualan->id,
            'print_url' => $printUrl
        ]);
    }
}
