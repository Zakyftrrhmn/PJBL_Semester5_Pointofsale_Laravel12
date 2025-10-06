<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Pemasok;
use App\Models\Penjualan;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter (harian, bulanan, tahunan)
        $filter = $request->get('filter', 'all');

        $queryPenjualan = Penjualan::query();
        $queryPembelian = Pembelian::query();

        // Terapkan filter berdasarkan pilihan user
        if ($filter === 'harian') {
            $queryPenjualan->whereDate('created_at', Carbon::today());
            $queryPembelian->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'bulanan') {
            $queryPenjualan->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
            $queryPembelian->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter === 'tahunan') {
            $queryPenjualan->whereYear('created_at', Carbon::now()->year);
            $queryPembelian->whereYear('created_at', Carbon::now()->year);
        }

        // Total berdasarkan filter
        $penjualan = $queryPenjualan->sum('total_bayar');
        $pembelian = $queryPembelian->sum('total_bayar');
        $profit = $penjualan - $pembelian;

        // Data master
        $totalProduk = Produk::count();
        $totalPelanggan = Pelanggan::count();
        $totalPemasok = Pemasok::count();

        return view('pages.dashboard.index', compact(
            'filter',
            'penjualan',
            'pembelian',
            'profit',
            'totalProduk',
            'totalPelanggan',
            'totalPemasok'
        ));
    }
}
