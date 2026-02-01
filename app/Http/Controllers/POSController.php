<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:penjualan.pos');
    }

    public function index(Request $request)
    {
        // Buat pelanggan "Umum" jika belum ada
        $pelangganUmum = Pelanggan::where('nama_pelanggan', 'Umum')->first();
        if (!$pelangganUmum) {
            $pelangganUmum = Pelanggan::create([
                'nama_pelanggan' => 'Umum',
                'telp' => '-',
                'alamat' => 'Tidak Ada',
            ]);
        }

        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get(['id', 'nama_pelanggan']);

        // Ambil semua produk aktif untuk JavaScript
        $produksForJs = Produk::where('is_active', 'active')
            ->orderBy('nama_produk')
            ->get()
            ->map(function ($produk) {
                return [
                    'id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'kode_produk' => $produk->kode_produk,
                    'harga_jual' => $produk->harga_jual,
                    'stok_produk' => $produk->stok_produk,
                    'photo_produk' => $produk->photo_produk,
                ];
            })
            ->toArray();

        // Data untuk pagination
        $produks = Produk::where('is_active', 'active')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%");
            })
            ->orderBy('nama_produk')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($produk) {
                return [
                    'id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'kode_produk' => $produk->kode_produk,
                    'harga_jual' => $produk->harga_jual,
                    'stok_produk' => $produk->stok_produk,
                    'photo_produk' => $produk->photo_produk,
                ];
            });

        return view('pages.pos.index', compact('produks', 'produksForJs', 'pelanggans', 'pelangganUmum'));
    }

    /**
     * ✅ STORE: Simpan transaksi DENGAN DISKON PER PRODUK
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'cart_data' => 'required|json',
                'pelanggan_id' => 'required|exists:pelanggans,id',
                'tanggal_penjualan' => 'required|date',
            ]);

            $request->merge([
                'pelanggan_id' => trim($request->pelanggan_id)
            ]);

            // Decode data cart
            $cart = json_decode($request->cart_data, true);
            if (empty($cart) || !is_array($cart)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Keranjang belanja kosong atau format tidak valid.')->withInput();
            }

            $subtotalAfterProductDiscounts = 0;
            $cartRecomputed = [];

            // ✅ PERBAIKAN: Hitung dengan DISKON PER PRODUK
            foreach ($cart as $item) {
                if (!isset($item['id']) || !isset($item['qty'])) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Data produk pada keranjang tidak lengkap.')->withInput();
                }

                $produk = Produk::lockForUpdate()->find($item['id']);
                if (!$produk) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Produk tidak ditemukan: ' . ($item['id'] ?? ''))->withInput();
                }

                $qty = (int) ($item['qty'] ?? 0);
                if ($qty < 1) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Jumlah produk minimal 1 untuk ' . $produk->nama_produk)->withInput();
                }

                if ($produk->stok_produk < $qty) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Stok tidak mencukupi untuk ' . $produk->nama_produk)->withInput();
                }

                // ✅ AMBIL DISKON DARI CART DATA
                $diskonItemPercent = (float) ($item['diskon_percent'] ?? 0);
                if ($diskonItemPercent < 0) $diskonItemPercent = 0;
                if ($diskonItemPercent > 100) $diskonItemPercent = 100;

                $hargaSatuan = (float) $produk->harga_jual;

                // ✅ HITUNG SUBTOTAL KOTOR (sebelum diskon item)
                $subtotalGross = $hargaSatuan * $qty;

                // ✅ HITUNG DISKON ITEM NOMINAL
                $diskonItemNominal = round(($diskonItemPercent / 100) * $subtotalGross);

                // ✅ SUBTOTAL BERSIH (setelah diskon item)
                $subtotalItem = $subtotalGross - $diskonItemNominal;

                // ✅ AKUMULASI ke subtotal keseluruhan
                $subtotalAfterProductDiscounts += $subtotalItem;

                $cartRecomputed[] = [
                    'produk_id' => $produk->id,
                    'qty' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'diskon_percent' => $diskonItemPercent,  // ✅ SIMPAN diskon item
                    'diskon_nominal' => $diskonItemNominal,  // ✅ SIMPAN nominal diskon
                    'subtotal' => $subtotalItem,             // ✅ Subtotal setelah diskon item
                ];
            }

            // Hitung diskon transaksi
            $diskonTransPercent = (float) ($request->diskon_percent ?? 0);
            if ($diskonTransPercent < 0) $diskonTransPercent = 0;
            if ($diskonTransPercent > 100) $diskonTransPercent = 100;

            $diskonTransNominal = round(($diskonTransPercent / 100) * $subtotalAfterProductDiscounts);
            $totalBayarComputed = $subtotalAfterProductDiscounts - $diskonTransNominal;
            if ($totalBayarComputed < 0) $totalBayarComputed = 0;

            // Simpan penjualan
            $penjualan = Penjualan::create([
                'kode_penjualan' => null,
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'total_harga' => $subtotalAfterProductDiscounts,
                'diskon_percent' => $diskonTransPercent,
                'diskon_nominal' => $diskonTransNominal,
                'total_bayar' => $totalBayarComputed,
                'jumlah_bayar' => $totalBayarComputed,
                'kembalian' => 0,
                'pelanggan_id' => $request->pelanggan_id,
                'user_id' => auth()->user()->id,
            ]);

            // ✅ Simpan detail penjualan DENGAN DISKON ITEM + update stok
            foreach ($cartRecomputed as $item) {
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_percent' => $item['diskon_percent'],  // ✅ SIMPAN
                    'diskon_nominal' => $item['diskon_nominal'],  // ✅ SIMPAN
                    'subtotal' => $item['subtotal'],
                ]);

                $produk = Produk::find($item['produk_id']);
                $produk->stok_produk -= $item['qty'];
                $produk->save();
            }

            DB::commit();

            return redirect()->route('invoice.show', $penjualan->id)
                ->with('success', 'Transaksi berhasil disimpan! Kode Transaksi: ' . $penjualan->kode_penjualan);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan server yang tidak diketahui.';
            Log::error('POS store error: ' . $errorMessage . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi. ' . $errorMessage)->withInput();
        }
    }
}
