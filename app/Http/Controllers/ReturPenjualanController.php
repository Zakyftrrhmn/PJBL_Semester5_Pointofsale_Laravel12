<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ReturPenjualan;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturPenjualanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:retur-penjualan.index')->only('index');
        $this->middleware('permission:retur-penjualan.create')->only(['create', 'store']);
    }

    public function index(Request $request)
    {
        $returs = ReturPenjualan::with(['penjualan.pelanggan', 'produk', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where('kode_retur', 'like', "%{$search}%")
                    ->orWhereHas('penjualan.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.retur_penjualan.index', compact('returs'));
    }

    public function create()
    {
        $penjualans = Penjualan::with('detailPenjualans')
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();

        return view('pages.retur_penjualan.create', compact('penjualans'));
    }

    public function getProdukByPenjualan(Request $request)
    {
        $penjualanId = $request->query('penjualan_id');

        if (!$penjualanId) return response()->json([]);

        $penjualan = Penjualan::find($penjualanId);
        if (!$penjualan) return response()->json([]);

        $details = DetailPenjualan::with('produk')->where('penjualan_id', $penjualanId)->get();
        $produkList = [];

        $totalHargaGross = $penjualan->total_harga; // total sebelum diskon
        $totalDiskon = $penjualan->diskon;
        $diskonRate = ($totalHargaGross > 0) ? $totalDiskon / $totalHargaGross : 0;
        $isDiskonApplied = $totalDiskon > 0;

        foreach ($details as $detail) {
            $totalReturSebelumnya = ReturPenjualan::where('penjualan_id', $penjualanId)
                ->where('produk_id', $detail->produk_id)
                ->sum('jumlah_retur');

            $sisaRetur = $detail->qty - $totalReturSebelumnya;

            $hargaSatuanNetto = $detail->harga_satuan * (1 - $diskonRate);

            if ($sisaRetur > 0) {
                $produkList[] = [
                    'id' => $detail->produk_id,
                    'nama_produk' => $detail->produk->nama_produk,
                    'kode_produk' => $detail->produk->kode_produk,
                    'harga_satuan' => round($hargaSatuanNetto, 2), // harga bersih (netto)
                    'harga_awal' => $detail->harga_satuan,
                    'qty_dijual' => $detail->qty,
                    'sisa_retur' => $sisaRetur,
                    'diskon_diterapkan' => $isDiskonApplied,
                ];
            }
        }

        return response()->json($produkList);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tanggal_retur' => 'required|date|before_or_equal:today',
            'penjualan_id' => 'required|uuid|exists:penjualans,id',
            'produk_id' => 'required|uuid|exists:produks,id',
            'jumlah_retur' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
            'alasan_retur' => 'required|string|max:255',
        ]);

        $nilaiRetur = $validatedData['jumlah_retur'] * $validatedData['harga_satuan'];

        $existingReturs = ReturPenjualan::where('penjualan_id', $validatedData['penjualan_id'])
            ->where('produk_id', $validatedData['produk_id'])
            ->sum('jumlah_retur');

        $detailPenjualan = DetailPenjualan::where('penjualan_id', $validatedData['penjualan_id'])
            ->where('produk_id', $validatedData['produk_id'])
            ->first();

        if (!$detailPenjualan || ($detailPenjualan->qty - $existingReturs) < $validatedData['jumlah_retur']) {
            return back()->with('error', 'Jumlah retur melebihi batas maksimal.')->withInput();
        }

        DB::beginTransaction();
        try {
            $kodeRetur = $this->generateKodeRetur();

            ReturPenjualan::create([
                'kode_retur' => $kodeRetur,
                'tanggal_retur' => $validatedData['tanggal_retur'],
                'penjualan_id' => $validatedData['penjualan_id'],
                'produk_id' => $validatedData['produk_id'],
                'jumlah_retur' => $validatedData['jumlah_retur'],
                'alasan_retur' => $validatedData['alasan_retur'],
                'nilai_retur' => $nilaiRetur,
                'user_id' => Auth::id(),
            ]);

            $produk = Produk::lockForUpdate()->find($validatedData['produk_id']);
            if (!$produk) {
                DB::rollBack();
                return back()->with('error', 'Produk tidak ditemukan.')->withInput();
            }

            $produk->stok_produk += $validatedData['jumlah_retur'];
            $produk->save();

            DB::commit();
            return redirect()->route('retur-penjualan.index')
                ->with('success', "Retur $kodeRetur berhasil disimpan. Nilai kembali: Rp " . number_format($nilaiRetur, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan retur: ' . $e->getMessage())->withInput();
        }
    }

    private function generateKodeRetur()
    {
        $prefix = 'RPJ' . Carbon::now()->format('Ym');
        $latest = ReturPenjualan::where('kode_retur', 'like', $prefix . '%')->latest('kode_retur')->first();
        $number = $latest ? ((int) substr($latest->kode_retur, -5)) + 1 : 1;
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
