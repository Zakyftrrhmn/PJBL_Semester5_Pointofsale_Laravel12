<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\ReturPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
{
    /**
     * Display a listing of the resource (Daftar Retur Pembelian).
     */
    public function index(Request $request)
    {
        $returs = ReturPembelian::with(['pembelian.pemasok', 'produk'])
            ->when($request->search, function ($query, $search) {
                $query->where('kode_retur', 'like', "%{$search}%")
                    ->orWhereHas('pembelian.pemasok', function ($q) use ($search) {
                        $q->where('nama_pemasok', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.retur_pembelian.index', compact('returs'));
    }

    /**
     * Show the form for creating a new resource (Form Retur).
     */
    public function create()
    {
        $pembelians = Pembelian::orderBy('kode_pembelian', 'desc')->get();
        return view('pages.retur_pembelian.create', compact('pembelians'));
    }

    /**
     * Get detail produk yang ada di transaksi pembelian
     */
    public function getProdukByPembelian($pembelianId)
    {
        $pembelian = Pembelian::with('detailPembelians.produk')->findOrFail($pembelianId);
        return response()->json($pembelian->detailPembelians->map(function ($detail) {
            return [
                'id'         => $detail->produk->id,
                'nama_produk' => $detail->produk->nama_produk,
                'harga_beli' => $detail->harga_beli,
                'max_retur'  => $detail->jumlah, // jumlah produk yang dibeli
            ];
        }));
    }

    /**
     * Store a newly created resource in storage (Simpan Retur).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_retur' => 'required|date',
            'pembelian_id'  => 'required|exists:pembelians,id',
            'produk_id'     => 'required|exists:produks,id',
            'jumlah_retur'  => 'required|integer|min:1',
            'alasan_retur'  => 'required|string|max:255',
            'harga_beli'    => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $nilaiRetur = $request->harga_beli * $request->jumlah_retur;

            // 1. Buat Data Retur Pembelian
            ReturPembelian::create([
                'tanggal_retur' => $request->tanggal_retur,
                'pembelian_id'  => $request->pembelian_id,
                'produk_id'     => $request->produk_id,
                'jumlah_retur'  => $request->jumlah_retur,
                'alasan_retur'  => $request->alasan_retur,
                'nilai_retur'   => $nilaiRetur,
            ]);

            // 2. Kurangi Stok Produk
            $produk = Produk::find($request->produk_id);
            $produk->stok_produk -= $request->jumlah_retur;
            $produk->save();

            DB::commit();

            return redirect()->route('retur-pembelian.index')->with('success', 'Retur Pembelian berhasil disimpan dan stok produk telah dikurangi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan retur pembelian: ' . $e->getMessage());
        }
    }
}
