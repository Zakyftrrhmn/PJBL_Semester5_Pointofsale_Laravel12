<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\DetailPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{

    public function __construct()
    {
        // Membatasi akses dengan middleware permission
        $this->middleware('permission:pembelian.index')->only('index');
        $this->middleware('permission:pembelian.create')->only(['create', 'store']);
        $this->middleware('permission:pembelian.show')->only('show');
        $this->middleware('permission:pembelian.edit')->only(['edit', 'update']);
        $this->middleware('permission:pembelian.destroy')->only('destroy');
        $this->middleware('permission:pembelian.export')->only(['exportExcel', 'exportPDF']);
    }
    /**
     * Show the form for creating a new resource (Pembelian Baru).
     */
    public function create()
    {
        $pemasoks = Pemasok::orderBy('nama_pemasok')->get();
        $produks  = Produk::where('is_active', 'active')->orderBy('nama_produk')->get();
        return view('pages.pembelian.create', compact('pemasoks', 'produks'));
    }

    /**
     * Store a newly created resource in storage (Pembelian Baru).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pembelian' => 'required|date',
            'pemasok_id'        => 'required|exists:pemasoks,id',
            'diskon'            => 'nullable|numeric|min:0',
            'ppn'               => 'nullable|numeric|min:0',
            'produk.*.id'       => 'required|exists:produks,id',
            'produk.*.harga_beli' => 'required|numeric|min:1',
            'produk.*.jumlah'   => 'required|integer|min:1',
        ], [
            'produk.*.id.required' => 'Setidaknya harus ada satu produk yang dibeli.',
        ]);

        try {
            DB::beginTransaction();

            // Hitung total harga
            $totalHarga = 0;
            foreach ($request->produk as $item) {
                $totalHarga += ($item['harga_beli'] * $item['jumlah']);
            }

            $diskon = $request->diskon ?? 0;
            $ppn    = $request->ppn ?? 0;
            $totalBayar = $totalHarga - $diskon + $ppn;

            // 1. Buat Header Pembelian
            $pembelian = Pembelian::create([
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'pemasok_id'        => $request->pemasok_id,
                'total_harga'       => $totalHarga,
                'diskon'            => $diskon,
                'ppn'               => $ppn,
                'total_bayar'       => $totalBayar,
            ]);

            // 2. Buat Detail Pembelian dan Update Stok Produk
            foreach ($request->produk as $item) {
                $subtotal = $item['harga_beli'] * $item['jumlah'];

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id'    => $item['id'],
                    'jumlah'       => $item['jumlah'],
                    'harga_beli'   => $item['harga_beli'],
                    'subtotal'     => $subtotal,
                ]);

                // Update Stok Produk
                $produk = Produk::find($item['id']);
                $produk->stok_produk += $item['jumlah'];
                $produk->save();
            }

            DB::commit();

            return redirect()->route('pesanan-pembelian.show', $pembelian->id)->with('success', 'Transaksi Pembelian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi pembelian: ' . $e->getMessage());
        }
    }
}
