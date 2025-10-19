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
                'telepon' => '-',
                'alamat' => 'Tidak Ada',
            ]);
        }

        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get(['id', 'nama_pelanggan']);

        $produks = Produk::where('is_active', 'active')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%");
            })
            ->orderBy('nama_produk')
            ->paginate(30)
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

        $produksForJs = $produks->items();

        return view('pages.pos.index', compact('produks', 'produksForJs', 'pelanggans', 'pelangganUmum'));
    }

    // ===============================
    // ========== STORE ==============
    // ===============================
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input utama
            $request->validate([
                'cart_data'     => 'required|json',
                'pelanggan_id'  => 'required|uuid|exists:pelanggans,id',
                'jumlah_bayar'  => 'required',
            ]);

            // Fungsi helper untuk membersihkan format uang
            $cleanCurrency = function ($value) {
                if ($value === null || $value === '') return 0.00;
                $value = (string)$value;
                $value = preg_replace('/[^0-9.,-]/', '', $value);
                if (str_contains($value, ',') && substr($value, -3, 1) == ',') {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                } else {
                    $value = str_replace(',', '', $value);
                }
                return (float) $value;
            };

            // Decode data cart
            $cart = json_decode($request->cart_data, true);
            if (empty($cart) || !is_array($cart)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Keranjang belanja kosong atau format tidak valid.')->withInput();
            }

            $subtotalAfterProductDiscounts = 0;
            $cartRecomputed = [];

            // Hitung ulang semua item
            foreach ($cart as $i => $item) {
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

                $hargaSatuan = (float) $produk->harga_jual;
                $diskonPercentItem = isset($item['diskon_percent']) ? (float)$item['diskon_percent'] : 0;
                if ($diskonPercentItem < 0) $diskonPercentItem = 0;
                if ($diskonPercentItem > 100) $diskonPercentItem = 100;

                $subtotalGross = $hargaSatuan * $qty;
                $diskonNominalItem = round(($diskonPercentItem / 100) * $subtotalGross);
                $subtotalAfterItem = $subtotalGross - $diskonNominalItem;

                if ($subtotalAfterItem < 0) $subtotalAfterItem = 0;

                $subtotalAfterProductDiscounts += $subtotalAfterItem;

                $cartRecomputed[] = [
                    'produk_id'       => $produk->id,
                    'qty'             => $qty,
                    'harga_satuan'    => $hargaSatuan,
                    'diskon_percent'  => $diskonPercentItem,
                    'diskon_nominal'  => $diskonNominalItem,
                    'subtotal'        => $subtotalAfterItem,
                ];
            }

            // Hitung diskon transaksi
            $diskonTransPercent = (float) ($request->diskon_percent ?? 0);
            if ($diskonTransPercent < 0) $diskonTransPercent = 0;
            if ($diskonTransPercent > 100) $diskonTransPercent = 100;

            $diskonTransNominal = round(($diskonTransPercent / 100) * $subtotalAfterProductDiscounts);
            $totalBayarComputed = $subtotalAfterProductDiscounts - $diskonTransNominal;
            if ($totalBayarComputed < 0) $totalBayarComputed = 0;

            // Bersihkan nilai bayar
            $jumlahBayar = $cleanCurrency($request->jumlah_bayar);

            // Toleransi selisih 1 rupiah
            if (abs($jumlahBayar - $totalBayarComputed) > 1) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Jumlah bayar tidak mencukupi. Total yang harus dibayar: Rp ' . number_format($totalBayarComputed, 0, ',', '.'))
                    ->withInput();
            }

            $kembalian = round($jumlahBayar - $totalBayarComputed, 2);

            // Simpan penjualan
            $penjualan = Penjualan::create([
                'kode_penjualan'    => null,
                'tanggal_penjualan' => now()->toDateString(),
                'total_harga'       => $subtotalAfterProductDiscounts,
                'diskon_percent'    => $diskonTransPercent,
                'diskon_nominal'    => $diskonTransNominal,
                'total_bayar'       => $totalBayarComputed,
                'jumlah_bayar'      => $jumlahBayar,
                'kembalian'         => $kembalian,
                'pelanggan_id'      => $request->pelanggan_id,
                'user_id'           => auth()->user()->id,
            ]);

            // Simpan detail penjualan + update stok
            foreach ($cartRecomputed as $item) {
                DetailPenjualan::create([
                    'penjualan_id'   => $penjualan->id,
                    'produk_id'      => $item['produk_id'],
                    'qty'            => $item['qty'],
                    'harga_satuan'   => $item['harga_satuan'],
                    'diskon_percent' => $item['diskon_percent'],
                    'diskon_nominal' => $item['diskon_nominal'],
                    'subtotal'       => $item['subtotal'],
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
