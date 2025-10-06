<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:penjualan.pos');
    }

    // Tampilkan Form POS
    public function index()
    {
        $produks = Produk::where('is_active', 'active')->orderBy('nama_produk')->get(['id', 'nama_produk', 'kode_produk', 'harga_jual', 'stok_produk', 'photo_produk']);
        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get(['id', 'nama_pelanggan']);

        // Pelanggan umum/default
        $pelangganUmum = Pelanggan::where('nama_pelanggan', 'Umum')->first();
        if (!$pelangganUmum) {
            // Opsional: Buat pelanggan 'Umum' jika belum ada
            $pelangganUmum = Pelanggan::create([
                'nama_pelanggan' => 'Umum',
                'telepon' => '-',
                'alamat' => 'Tidak Ada',
            ]);
        }


        return view('pages.pos.index', compact('produks', 'pelanggans', 'pelangganUmum'));
    }

    // Proses Simpan Transaksi
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. Validasi Input
            // Ubah validasi menjadi string karena mungkin dikirim sebagai string berformat
            $request->validate([
                'cart_data'     => 'required|json', // Data keranjang dalam format JSON
                'total_harga'   => 'required|string',
                'diskon'        => 'nullable|string',
                'total_bayar'   => 'required|string',
                'jumlah_bayar'  => 'required|string',
                'kembalian'     => 'nullable|string',
                'pelanggan_id'  => 'required|uuid|exists:pelanggans,id',
            ]);

            // --- FUNGSI PEMBERSIH MATA UANG (PENTING UNTUK MENGHINDARI ERROR DATABASE) ---
            $cleanCurrency = function ($value) {
                // Jika nilai null atau kosong, kembalikan 0.00
                if (empty($value)) return 0.00;

                // Hapus semua karakter non-angka kecuali titik dan koma
                $value = preg_replace('/[^0-9.,]/', '', $value);

                // Jika format menggunakan koma sebagai desimal (e.g., 1.000,00 -> 1000.00)
                if (str_contains($value, ',') && substr($value, -3, 1) == ',') {
                    $value = str_replace('.', '', $value); // Hapus pemisah ribuan
                    $value = str_replace(',', '.', $value); // Ganti koma ke titik
                }
                // Jika format menggunakan titik sebagai desimal dan koma sebagai ribuan (jarang di Indo, tapi untuk jaga-jaga)
                else {
                    $value = str_replace('.', '', $value); // Asumsi titik adalah pemisah ribuan
                }
                return (float) $value;
            };

            // Bersihkan dan konversi data
            $totalHarga  = $cleanCurrency($request->total_harga);
            $diskon      = $cleanCurrency($request->diskon ?? 0);
            $totalBayar  = $cleanCurrency($request->total_bayar);
            $jumlahBayar = $cleanCurrency($request->jumlah_bayar);
            $kembalian   = $cleanCurrency($request->kembalian ?? 0);

            // Validasi: Uang bayar harus cukup
            if ($jumlahBayar < $totalBayar) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal menyimpan transaksi. Jumlah bayar tidak mencukupi.')->withInput();
            }

            $cart = json_decode($request->cart_data, true);

            if (empty($cart)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal menyimpan transaksi. Keranjang belanja kosong.')->withInput();
            }

            // 2. Simpan Penjualan
            $penjualan = Penjualan::create([
                'kode_penjualan'    => null, // Akan digenerate di model
                'tanggal_penjualan' => now()->toDateString(),
                'total_harga'       => $totalHarga,
                'diskon'            => $diskon,
                'total_bayar'       => $totalBayar,
                'jumlah_bayar'      => $jumlahBayar,
                'kembalian'         => $kembalian,
                'pelanggan_id'      => $request->pelanggan_id,
                'user_id'           => auth()->user()->id,
            ]);

            // 3. Simpan Detail Penjualan & Update Stok
            foreach ($cart as $item) {
                $produk = Produk::lockForUpdate()->find($item['id']); // Lock produk saat update stok

                if (!$produk || $produk->stok_produk < $item['qty']) {
                    DB::rollBack();
                    $produkName = $produk ? $produk->nama_produk : 'Produk Tidak Ditemukan';
                    return redirect()->back()->with('error', 'Gagal menyimpan transaksi. Stok untuk ' . $produkName . ' tidak mencukupi.')->withInput();
                }

                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => $produk->id,
                    'qty'          => $item['qty'],
                    // Pastikan harga_satuan dan subtotal menggunakan nilai dari model (sudah bersih)
                    'harga_satuan' => $produk->harga_jual,
                    'subtotal'     => $produk->harga_jual * $item['qty'],
                ]);

                // Update Stok Produk
                $produk->stok_produk -= $item['qty'];
                $produk->save();
            }

            DB::commit();

            // Redirect ke halaman detail/print struk yang baru
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('success', 'Transaksi berhasil disimpan! Kode Transaksi: ' . $penjualan->kode_penjualan);
        } catch (\Exception $e) {
            DB::rollBack();
            // Aktifkan Log ini jika masih terjadi error di production untuk melihat detailnya
            // Log::error('POS Transaction Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            // Berikan pesan error yang lebih informatif jika ada pesan dari exception, atau pesan umum
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan server yang tidak diketahui.';
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi. ' . $errorMessage)->withInput();
        }
    }
}
