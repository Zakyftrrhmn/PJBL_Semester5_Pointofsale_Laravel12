<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\ReturPenjualan;
use App\Models\ReturPembelian;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Pemasok;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard.index');
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'daily');
        $now = Carbon::now();

        $startDate = match ($filter) {
            'daily' => $now->copy()->startOfDay(),
            'monthly' => $now->copy()->startOfMonth(),
            'yearly' => $now->copy()->startOfYear(),
            default => Carbon::create(2000, 1, 1),
        };

        // =================================================================
        // 1. METRIK TRANSAKSI & LABA
        // =================================================================

        $totalPenjualan = Penjualan::where('created_at', '>=', $startDate)->sum('total_bayar');
        $totalReturPenjualan = ReturPenjualan::where('created_at', '>=', $startDate)->sum('nilai_retur');
        $penjualanBersih = $totalPenjualan - $totalReturPenjualan;
        $totalPembelian = Pembelian::where('created_at', '>=', $startDate)->sum('total_bayar');
        $totalReturPembelian = ReturPembelian::where('created_at', '>=', $startDate)->sum('nilai_retur');
        $labaBersih = $penjualanBersih - ($totalPembelian - $totalReturPembelian);

        // =================================================================
        // 2. METRIK MASTER DATA
        // =================================================================

        $jumlahProduk = Produk::count();
        $jumlahPelanggan = Pelanggan::count();
        $jumlahPemasok = Pemasok::count();
        $jumlahUser = User::count();
        $jumlahInvoice = Penjualan::count();

        // =================================================================
        // 3. WARNING STOK (Low Stock Products)
        // =================================================================

        $stokHampirHabis = Produk::whereColumn('stok_produk', '<=', 'pengingat_stok')
            ->orderBy('stok_produk', 'asc')
            ->take(5)
            ->get();
        $countStokHampirHabis = Produk::whereColumn('stok_produk', '<=', 'pengingat_stok')->count();

        // =================================================================
        // 4. TOP SELLING PRODUCTS (***PERBAIKAN TERAKHIR DI SINI***)
        // =================================================================

        $topSellingProducts = Penjualan::join('detail_penjualans', 'penjualans.id', '=', 'detail_penjualans.penjualan_id')
            ->join('produks', 'detail_penjualans.produk_id', '=', 'produks.id')
            ->select(
                'produks.id',
                'produks.nama_produk',
                'produks.harga_jual',
                // UBAH DARI SUM(detail_penjualans.jumlah)
                DB::raw('SUM(detail_penjualans.qty) as total_terjual') // <-- ASUMSI: Nama kolom kuantitas adalah 'qty'
            )
            ->where('penjualans.created_at', '>=', $startDate)
            ->groupBy('produks.id', 'produks.nama_produk', 'produks.harga_jual')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();

        // =================================================================
        // 5. DATA UNTUK CHART PENJUALAN BERSIH
        // =================================================================

        $dataChart = Penjualan::select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('SUM(total_bayar) as total_penjualan_bruto')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get()
            ->keyBy('tanggal');

        $dataRetur = ReturPenjualan::select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('SUM(nilai_retur) as total_retur')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $labels = [];
        $netSalesData = [];
        $allDates = $dataChart->keys()->merge($dataRetur->keys())->unique()->sort();

        foreach ($allDates as $date) {
            $bruto = $dataChart->get($date)['total_penjualan_bruto'] ?? 0;
            $retur = $dataRetur->get($date)['total_retur'] ?? 0;
            $net = $bruto - $retur;

            if ($filter == 'daily' || $filter == 'all') {
                $labels[] = Carbon::parse($date)->format('D, d M');
            } else if ($filter == 'monthly') {
                $labels[] = Carbon::parse($date)->format('d M');
            } else {
                $labels[] = Carbon::parse($date)->format('M Y');
            }

            $netSalesData[] = $net;
        }


        return view('pages.dashboard.index', [
            'filter' => $filter,
            'totalPenjualan' => $totalPenjualan,
            'totalReturPenjualan' => $totalReturPenjualan,
            'penjualanBersih' => $penjualanBersih,
            'totalPembelian' => $totalPembelian,
            'totalReturPembelian' => $totalReturPembelian,
            'labaBersih' => $labaBersih,
            'jumlahProduk' => $jumlahProduk,
            'jumlahPelanggan' => $jumlahPelanggan,
            'jumlahPemasok' => $jumlahPemasok,
            'jumlahInvoice' => $jumlahInvoice,
            'jumlahUser' => $jumlahUser,
            'stokHampirHabis' => $stokHampirHabis,
            'countStokHampirHabis' => $countStokHampirHabis,
            'topSellingProducts' => $topSellingProducts,
            'chartLabels' => $labels,
            'chartData' => $netSalesData,
        ]);
    }
}
