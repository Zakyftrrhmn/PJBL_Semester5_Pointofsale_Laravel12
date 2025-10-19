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

    /**
     * Mengambil produk dari transaksi dan menghitung harga per unit netto sesuai diskon transaksi.
     */
    public function getProdukByPenjualan(Request $request)
    {
        $penjualanId = $request->query('penjualan_id');
        if (!$penjualanId) return response()->json([]);

        $penjualan = Penjualan::with('detailPenjualans.produk')->find($penjualanId);
        if (!$penjualan) return response()->json([]);

        // Ambil rate diskon transaksi
        $totalHargaAfterItemDiscount = $penjualan->total_harga ?? 0;
        $diskonTransaksiNominal = $penjualan->diskon_nominal ?? 0;
        $diskonRateTransaksi = ($totalHargaAfterItemDiscount > 0)
            ? ($diskonTransaksiNominal / $totalHargaAfterItemDiscount)
            : 0;

        $produkList = [];

        foreach ($penjualan->detailPenjualans as $detail) {
            $totalReturSebelumnya = ReturPenjualan::where('penjualan_id', $penjualanId)
                ->where('produk_id', $detail->produk_id)
                ->sum('jumlah_retur');

            $sisaRetur = $detail->qty - $totalReturSebelumnya;
            if ($sisaRetur <= 0) continue;

            // Harga per unit setelah diskon item
            $hargaPerUnit = $detail->qty > 0 ? ($detail->subtotal / $detail->qty) : $detail->harga_satuan;

            // Terapkan diskon transaksi secara proporsional
            $hargaNettoPerUnit = $hargaPerUnit * (1 - $diskonRateTransaksi);

            $produkList[] = [
                'id' => $detail->produk_id,
                'kode_produk' => $detail->produk->kode_produk ?? null,
                'nama_produk' => $detail->produk->nama_produk ?? 'Produk tidak ditemukan',
                'qty_dijual' => $detail->qty,
                'sisa_retur' => (int) $sisaRetur,
                'harga_satuan' => (float) round($hargaNettoPerUnit, 2),
            ];
        }

        return response()->json($produkList);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_retur' => 'required|date|before_or_equal:today',
            'penjualan_id' => 'required|uuid|exists:penjualans,id',
            'retur_items' => 'required|array|min:1',
            'retur_items.*.produk_id' => 'required|uuid|exists:produks,id',
            'retur_items.*.jumlah_retur' => 'required|integer|min:1',
            'retur_items.*.harga_satuan' => 'required|numeric|min:0',
            'retur_items.*.alasan_retur' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = Penjualan::findOrFail($validated['penjualan_id']);
            $kodeRetur = $this->generateKodeRetur(); // hanya sekali per transaksi retur

            $totalNilaiRetur = 0;

            foreach ($validated['retur_items'] as $item) {
                $detail = DetailPenjualan::where('penjualan_id', $penjualan->id)
                    ->where('produk_id', $item['produk_id'])
                    ->first();

                if (!$detail) continue;

                $existingRetur = ReturPenjualan::where('penjualan_id', $penjualan->id)
                    ->where('produk_id', $item['produk_id'])
                    ->sum('jumlah_retur');

                $sisa = $detail->qty - $existingRetur;
                if ($item['jumlah_retur'] > $sisa) {
                    DB::rollBack();
                    return back()->with('error', "Jumlah retur untuk produk {$detail->produk->nama_produk} melebihi batas.")->withInput();
                }

                $nilaiRetur = $item['jumlah_retur'] * $item['harga_satuan'];
                $totalNilaiRetur += $nilaiRetur;

                // Simpan retur produk (masih 1 kode_retur yang sama)
                ReturPenjualan::create([
                    'kode_retur' => $kodeRetur,
                    'tanggal_retur' => $validated['tanggal_retur'],
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah_retur' => $item['jumlah_retur'],
                    'alasan_retur' => $item['alasan_retur'],
                    'nilai_retur' => $nilaiRetur,
                    'user_id' => Auth::id(),
                ]);

                // Update stok
                $produk = Produk::lockForUpdate()->find($item['produk_id']);
                if ($produk) {
                    $produk->stok_produk += $item['jumlah_retur'];
                    $produk->save();
                }
            }

            // Cek total nilai retur
            if ($totalNilaiRetur > $penjualan->total_bayar) {
                DB::rollBack();
                return back()->with('error', 'Total nilai retur melebihi total pembayaran penjualan!')->withInput();
            }

            DB::commit();

            return redirect()->route('retur-penjualan.index')
                ->with('success', "Retur {$kodeRetur} berhasil disimpan dengan total: Rp " . number_format($totalNilaiRetur, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan retur: ' . $e->getMessage())->withInput();
        }
    }


    private function generateKodeRetur()
    {
        $prefix = 'RPJ' . date('Ym'); // contoh: RPJ202510

        // Ambil kode terakhir yang paling besar dari database
        $latestKode = DB::table('retur_penjualans')
            ->where('kode_retur', 'like', $prefix . '%')
            ->orderByDesc('kode_retur')
            ->value('kode_retur'); // ambil langsung nilainya

        if ($latestKode) {
            // Ambil 5 digit terakhir sebagai angka urut
            $lastNumber = intval(substr($latestKode, -5));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Kembalikan kode baru
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
