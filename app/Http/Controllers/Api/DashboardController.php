<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Pastikan model-model ini sudah ada di folder App/Models
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Pelanggan;

class DashboardController extends Controller
{
    /**
     * Mengambil data metrik dan ringkasan untuk Dashboard Mobile.
     * Dapat menerima parameter filter: 'daily', 'monthly', 'yearly', atau 'all'.
     */
    public function index(Request $request)
    {
        // 1. Ambil dan Tentukan Filter Periode
        $filter = $request->get('filter', 'daily'); // Default: daily
        $now = Carbon::now();

        $startDate = match ($filter) {
            'daily' => $now->copy()->startOfDay(),
            'monthly' => $now->copy()->startOfMonth(),
            'yearly' => $now->copy()->startOfYear(),
            default => Carbon::create(2000, 1, 1), // 'all' time
        };

        // 2. Perhitungan Metrik Transaksi

        // Total Penjualan (Kotor)
        $totalPenjualan = Penjualan::where('created_at', '>=', $startDate)->sum('total_bayar');

        // Total Pembelian (Untuk indikator kas keluar)
        $totalPembelian = 0;
        // Hanya hitung Pembelian jika modelnya ada
        if (class_exists(Pembelian::class)) {
            $totalPembelian = Pembelian::where('created_at', '>=', $startDate)->sum('total_bayar');
        }

        // Jumlah Invoice Penjualan
        $jumlahInvoice = Penjualan::where('created_at', '>=', $startDate)->count();

        // 3. Perhitungan Metrik Statistik

        // Jumlah Produk Aktif
        $jumlahProduk = Produk::where('is_active', 'active')->count();

        // Jumlah Pelanggan
        $jumlahPelanggan = Pelanggan::count();

        // Stok Hampir Habis (Low Stock: stok_produk <= pengingat_stok)
        $countStokHampirHabis = Produk::where('is_active', 'active')
            ->whereColumn('stok_produk', '<=', 'pengingat_stok')
            ->count();

        // 4. Produk Paling Laris (Top 5 berdasarkan kuantitas)
        $topSellingProducts = DB::table('detail_penjualans')
            ->select(DB::raw('produk_id, SUM(qty) as total_qty_terjual'))
            ->join('penjualans', 'detail_penjualans.penjualan_id', '=', 'penjualans.id')
            ->where('penjualans.created_at', '>=', $startDate)
            ->groupBy('produk_id')
            ->orderByDesc('total_qty_terjual')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                // Ambil nama produk
                $produk = Produk::find($item->produk_id);
                return [
                    'nama_produk' => $produk ? $produk->nama_produk : 'Produk Dihapus',
                    'total_qty_terjual' => (int) $item->total_qty_terjual,
                ];
            });


        // 5. Kembalikan Data dalam Format JSON
        return response()->json([
            'filter_periode' => $filter,
            'start_date' => $startDate->toDateString(),
            'metrics' => [
                'total_penjualan' => (float) $totalPenjualan,
                'total_pembelian' => (float) $totalPembelian, // Akan 0 jika Pembelian tidak ada
                'jumlah_invoice' => (int) $jumlahInvoice,
            ],
            'statistics' => [
                'jumlah_produk_aktif' => (int) $jumlahProduk,
                'jumlah_pelanggan' => (int) $jumlahPelanggan,
                'stok_hampir_habis' => (int) $countStokHampirHabis,
            ],
            'top_selling_products' => $topSellingProducts,
        ], 200);
    }
}
