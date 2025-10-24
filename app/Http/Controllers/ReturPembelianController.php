<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\ReturPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:retur-pembelian.index')->only('index');
        $this->middleware('permission:retur-pembelian.create')->only(['create', 'store']);
    }

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

    public function create()
    {
        $pembelians = Pembelian::orderBy('kode_pembelian', 'desc')->get();
        return view('pages.retur_pembelian.create', compact('pembelians'));
    }

    public function getProdukByPembelian($pembelianId)
    {
        $pembelian = Pembelian::with('detailPembelians.produk')->findOrFail($pembelianId);

        $totalDiskon = $pembelian->diskon ?? 0;
        $totalHargaBruto = $pembelian->total_harga_bruto ?? $pembelian->detailPembelians->sum(function ($d) {
            return ($d->subtotal ?? ($d->harga_beli * $d->jumlah));
        });

        $data = $pembelian->detailPembelians->map(function ($detail) use ($totalDiskon, $totalHargaBruto, $pembelianId) {
            $detailSubtotal = $detail->subtotal ?? ($detail->harga_beli * $detail->jumlah);
            $diskonPerProduk = $totalHargaBruto > 0 ? ($detailSubtotal / $totalHargaBruto) * $totalDiskon : 0;
            $hargaBersihPerUnit = $detail->jumlah > 0 ? (($detailSubtotal - $diskonPerProduk) / $detail->jumlah) : 0;
            $sudahDiretur = ReturPembelian::where('pembelian_id', $pembelianId)
                ->where('produk_id', $detail->produk->id)
                ->sum('jumlah_retur');
            $maxRetur = max(0, ($detail->jumlah ?? 0) - $sudahDiretur);

            return [
                'id' => $detail->produk->id,
                'nama_produk' => $detail->produk->nama_produk,
                'harga_beli' => round($hargaBersihPerUnit, 2),
                'max_retur' => (int) $maxRetur,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_retur' => 'required|date',
            'pembelian_id'  => 'required|exists:pembelians,id',
            'produk_id'     => 'required|exists:produks,id',
            'jumlah_retur'  => 'required|integer|min:1',
            'alasan_retur'  => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $detail = DB::table('detail_pembelians')
                ->where('pembelian_id', $request->pembelian_id)
                ->where('produk_id', $request->produk_id)
                ->first();

            if (!$detail) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Produk tidak ditemukan dalam transaksi pembelian ini.');
            }

            $totalReturSebelumnya = ReturPembelian::where('pembelian_id', $request->pembelian_id)
                ->where('produk_id', $request->produk_id)
                ->sum('jumlah_retur');

            if ($totalReturSebelumnya + $request->jumlah_retur > $detail->jumlah) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Jumlah retur melebihi jumlah produk yang dibeli.');
            }

            $pembelian = Pembelian::with('detailPembelians')->findOrFail($request->pembelian_id);
            $totalDiskon = $pembelian->diskon ?? 0;
            $totalHargaBruto = $pembelian->total_harga_bruto ?? $pembelian->detailPembelians->sum(function ($d) {
                return ($d->subtotal ?? ($d->harga_beli * $d->jumlah));
            });

            $detailModel = collect($pembelian->detailPembelians)->firstWhere('produk_id', $request->produk_id);
            $detailSubtotal = $detailModel->subtotal ?? ($detailModel->harga_beli * $detailModel->jumlah);
            $diskonPerProduk = $totalHargaBruto > 0 ? ($detailSubtotal / $totalHargaBruto) * $totalDiskon : 0;
            $hargaBersihPerUnit = $detailModel->jumlah > 0 ? ($detailSubtotal - $diskonPerProduk) / $detailModel->jumlah : 0;
            $nilaiRetur = $hargaBersihPerUnit * $request->jumlah_retur;

            // 1. Buat data Retur Pembelian
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
            $produk->stok_produk = max(0, ($produk->stok_produk ?? 0) - $request->jumlah_retur);
            $produk->save();

            // CATATAN: Logika pembaruan status Pembelian (Completed/Partially Returned/Returned)
            // telah dipindahkan ke Pembelian Model sebagai Accessor (getStatusAttribute)
            // untuk mendapatkan status secara dinamis.

            DB::commit();
            return redirect()->route('retur-pembelian.index')->with('success', 'Retur Pembelian berhasil disimpan dan stok produk telah dikurangi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan retur pembelian: ' . $e->getMessage());
        }
    }
}
