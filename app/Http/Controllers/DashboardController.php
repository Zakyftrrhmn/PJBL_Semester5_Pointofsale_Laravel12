<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Penjualan;
use App\Models\Pembelian;
// use App\Models\ReturPenjualan; // <-- dikomentar karena retur penjualan
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

        // $totalReturPenjualan = ReturPenjualan::where('created_at', '>=', $startDate)->sum('nilai_retur'); // dikomentar
        // $penjualanBersih = $totalPenjualan - $totalReturPenjualan; // dikomentar

        $penjualanBersih = $totalPenjualan; // sebagai pengganti biar tidak error

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
        // 3. WARNING STOK
        // =================================================================

        $stokHampirHabis = Produk::whereColumn('stok_produk', '<=', 'pengingat_stok')
            ->orderBy('stok_produk', 'asc')
            ->take(5)
            ->get();
        $countStokHampirHabis = Produk::whereColumn('stok_produk', '<=', 'pengingat_stok')->count();

        // =================================================================
        // 4. TOP SELLING PRODUCTS
        // =================================================================

        $topSellingProducts = Penjualan::join('detail_penjualans', 'penjualans.id', '=', 'detail_penjualans.penjualan_id')
            ->join('produks', 'detail_penjualans.produk_id', '=', 'produks.id')
            ->select(
                'produks.id',
                'produks.nama_produk',
                'produks.harga_jual',
                'produks.photo_produk',
                DB::raw('SUM(detail_penjualans.qty) as total_terjual')
            )
            ->where('penjualans.created_at', '>=', $startDate)
            ->groupBy('produks.id', 'produks.nama_produk', 'produks.harga_jual', 'produks.photo_produk')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();


        // =================================================================
        // 4B. TOP CUSTOMERS
        // =================================================================

        $topCustomers = Penjualan::join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id')
            ->select(
                'pelanggans.id',
                'pelanggans.nama_pelanggan',
                'pelanggans.telp as nomor_hp',
                'pelanggans.photo_pelanggan',
                DB::raw('SUM(penjualans.total_bayar) as total_belanja'),
                DB::raw('COUNT(penjualans.id) as total_transaksi')
            )
            ->where('penjualans.created_at', '>=', $startDate)
            ->groupBy('pelanggans.id', 'pelanggans.nama_pelanggan', 'pelanggans.telp', 'pelanggans.photo_pelanggan')
            ->orderByDesc('total_belanja')
            ->take(5)
            ->get();

        // =================================================================
        // 4C. TOP SUPPLIERS (FINAL FIX: Menggunakan Subquery untuk Pembelian Bersih - NET VALUE)
        // =================================================================

        $topSuppliers = Pemasok::select('id', 'nama_pemasok')

            // 1. Ambil Total Pembelian Bruto (Dari kolom total_bayar yang menyimpan nilai awal/bruto)
            ->addSelect([
                'gross_value' => Pembelian::selectRaw('COALESCE(SUM(total_bayar), 0)')
                    ->whereColumn('pemasok_id', 'pemasoks.id')
                    ->where('created_at', '>=', $startDate),
            ])

            // 2. Ambil Total Nilai Retur (Menggunakan Subquery untuk menghindari duplikasi/fanout)
            ->addSelect([
                'retur_value' => ReturPembelian::join('pembelians', 'retur_pembelians.pembelian_id', '=', 'pembelians.id')
                    ->selectRaw('COALESCE(SUM(retur_pembelians.nilai_retur), 0)')
                    ->whereColumn('pembelians.pemasok_id', 'pemasoks.id')
                    // Filter tanggal pada transaksi pembelian yang terkait
                    ->where('pembelians.created_at', '>=', $startDate),
            ])

            // 3. Ambil Total Transaksi (Count)
            ->addSelect([
                'total_transaksi' => Pembelian::selectRaw('COUNT(id)')
                    ->whereColumn('pemasok_id', 'pemasoks.id')
                    ->where('created_at', '>=', $startDate),
            ])

            // 4. Filter Utama: Hanya tampilkan Pemasok yang memiliki transaksi pada periode ini
            // Menggunakan whereIn untuk menghindari error relasi Pemasok::pembelians()
            ->whereIn('id', function ($query) use ($startDate) {
                $query->select('pemasok_id')
                    ->from('pembelians')
                    ->where('created_at', '>=', $startDate);
            })
            ->get()

            // 5. Hitung Nilai Bersih (Net Purchase Value) di PHP (Collection)
            ->map(function ($supplier) {
                // total_pembelian = Gross Value - Retur Value (Nilai yang akan dibaca di Blade)
                $supplier->total_pembelian = $supplier->gross_value - $supplier->retur_value;
                return $supplier;
            })

            // 6. Urutkan kembali berdasarkan Nilai Bersih dan Ambil 5 Teratas
            ->sortByDesc('total_pembelian')
            ->take(5)
            ->values();

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

        // $dataRetur = ReturPenjualan::select(
        //     DB::raw('DATE(created_at) as tanggal'),
        //     DB::raw('SUM(nilai_retur) as total_retur')
        // )
        //     ->where('created_at', '>=', $startDate)
        //     ->groupBy('tanggal')
        //     ->get()
        //     ->keyBy('tanggal');

        $labels = [];
        $netSalesData = [];
        // $allDates = $dataChart->keys()->merge($dataRetur->keys())->unique()->sort(); // dikomentar
        $allDates = $dataChart->keys(); // pengganti

        foreach ($allDates as $date) {
            $bruto = $dataChart->get($date)['total_penjualan_bruto'] ?? 0;
            // $retur = $dataRetur->get($date)['total_retur'] ?? 0; // dikomentar
            $retur = 0; // placeholder
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
            // 'totalReturPenjualan' => $totalReturPenjualan, // dikomentar
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
            'topCustomers' => $topCustomers,
            'topSuppliers' => $topSuppliers, // <-- Variabel baru yang ditambahkan
            'chartLabels' => $labels,
            'chartData' => $netSalesData,
        ]);
    }
}
