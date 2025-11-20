<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DetailPenjualan; // DITAMBAHKAN
use App\Models\Produk; // DITAMBAHKAN
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\Terbilang;
use Illuminate\Support\Facades\DB; // DITAMBAHKAN
use Illuminate\Support\Facades\Log; // DITAMBAHKAN

class InvoiceController extends Controller
{
    public function __construct()
    {
        // Revisi Middleware
        $this->middleware('can:invoice.index')->only('index', 'show');
        $this->middleware('can:invoice.export')->only('printNoDiscount', 'printWithDiscount');
        $this->middleware('can:invoice.edit')->only('edit'); // Hanya 'edit' yang diperlukan
        $this->middleware('can:invoice.destroy')->only('destroy'); // DITAMBAHKAN
    }

    /**
     * Tampilkan daftar riwayat transaksi (Penjualan).
     */
    public function index(Request $request)
    {
        $penjualans = Penjualan::with(['pelanggan', 'user', 'returPenjualans'])
            ->when($request->search, function ($query, $search) {
                $query->where('kode_penjualan', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.invoice.index', compact('penjualans'));
    }

    /**
     * Tampilkan detail transaksi (Invoice).
     */
    public function show(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');
        return view('pages.invoice.show', compact('penjualan'));
    }

    /**
     * Helper: Hitung Total Harga Murni Tanpa Diskon Item
     * (Harga Satuan * Qty)
     */
    protected function calculateSubtotalMurni(Penjualan $penjualan)
    {
        return $penjualan->detailPenjualans->sum(fn($d) => $d->qty * $d->harga_satuan);
    }

    /**
     * Helper: Hitung Total Harga Setelah Diskon Item
     * (Menggunakan kolom total_harga di Penjualan, yang sudah termasuk diskon item)
     */
    protected function calculateSubtotalAfterItemDiscount(Penjualan $penjualan)
    {
        return $penjualan->total_harga; // total_harga adalah total setelah diskon item, sebelum diskon transaksi
    }


    /**
     * Cetak invoice TANPA diskon (Harga Murni).
     * Item: Harga Satuan * Qty (Diskon item diabaikan).
     * Transaksi: Diskon transaksi diabaikan.
     */
    public function printNoDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');
        $isDiscountApplied = false;

        // TOTAL MURNI UNTUK INVOICE
        $subTotalAwal = $this->calculateSubtotalMurni($penjualan);
        $totalFinal = $subTotalAwal; // Total murni semua item

        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalFinal;

        // Tambahkan variabel item_total_type untuk mengontrol di view
        $item_total_type = 'MURNI';

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalFinal', 'bayar', 'kembalian', 'item_total_type');

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, 595, 420], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'chroot' => public_path(), 'defaultFont' => 'Courier New']);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-TanpaDiskon.pdf');
    }

    /**
     * Cetak invoice DENGAN diskon.
     * Item: Subtotal (Diskon item diperhitungkan).
     * Transaksi: Diskon transaksi diperhitungkan.
     */
    public function printWithDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');
        $isDiscountApplied = true;

        // TOTAL SETELAH DISKON ITEM (menggunakan total_harga dari database)
        $subTotalAwal = $this->calculateSubtotalAfterItemDiscount($penjualan);

        // Total Final adalah total_bayar dari database (setelah diskon transaksi)
        $totalFinal = $penjualan->total_bayar;

        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalFinal;

        // Tambahkan variabel item_total_type untuk mengontrol di view
        $item_total_type = 'DISKON'; // Subtotal item yang sudah terdiskon

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalFinal', 'bayar', 'kembalian', 'item_total_type');

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, 595, 420], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'chroot' => public_path(), 'defaultFont' => 'Courier New']);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
    }

    public function edit(Penjualan $penjualan)
    {
        // Pengecekan status: Tidak bisa edit yang sudah diretur
        if ($penjualan->status === 'Returned') {
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('error', 'Transaksi yang telah diretur tidak dapat diedit.');
        }

        // Siapkan data untuk halaman POS
        $pelangganUmum = \App\Models\Pelanggan::where('nama_pelanggan', 'Umum')->first();

        $pelanggans = \App\Models\Pelanggan::orderBy('nama_pelanggan')->get(['id', 'nama_pelanggan']);

        // Ambil semua produk yang aktif untuk list di sisi kiri (sama seperti index POS)
        $produks = \App\Models\Produk::where('is_active', 'active')
            ->orderBy('nama_produk')
            ->paginate(30);

        // Ambil semua produk untuk Alpine.js (tanpa pagination)
        $produksForJs = \App\Models\Produk::where('is_active', 'active')->get()->map(function ($produk) {
            return [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'kode_produk' => $produk->kode_produk,
                'harga_jual' => $produk->harga_jual,
                'stok_produk' => $produk->stok_produk,
                'photo_produk' => $produk->photo_produk,
            ];
        })->toArray();


        // Ambil data keranjang dari detail penjualan yang sudah ada
        $initialCart = $penjualan->detailPenjualans->map(function ($detail) {
            return [
                'id' => $detail->produk_id,
                'nama_produk' => $detail->produk->nama_produk,
                'kode_produk' => $detail->produk->kode_produk,
                'harga_satuan' => (float) $detail->harga_satuan,
                // Stok produk harus diambil dari produk *saat ini* di database
                'stok_produk' => $detail->produk->stok_produk + $detail->qty, // Tambahkan stok kembali untuk perhitungan max qty edit
                'qty' => (int) $detail->qty,
                'subtotal' => (float) $detail->subtotal,
                'diskon_percent' => (float) $detail->diskon_percent,
                'diskon_nominal' => (float) $detail->diskon_nominal,
            ];
        })->toArray();


        // Kirim semua data ke view 'pages.pos.index'
        return view('pages.pos.index', compact(
            'produks',
            'produksForJs',
            'pelanggans',
            'pelangganUmum',
            'penjualan' // Kirim objek Penjualan untuk data lama
        ))
            // Gunakan fungsi with() untuk mengirim data yang hanya dipakai saat edit
            ->with('isEditMode', true)
            ->with('initialCart', $initialCart)
            ->with('initialPelangganId', $penjualan->pelanggan_id)
            ->with('initialDiskonPercent', $penjualan->diskon_percent)
            ->with('initialJumlahBayar', $penjualan->jumlah_bayar);
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        // Pengecekan status: Tidak bisa update yang sudah diretur
        if ($penjualan->status === 'Returned') {
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('error', 'Transaksi yang telah diretur tidak dapat diubah.');
        }

        DB::beginTransaction();

        try {
            // Validasi input utama (sama seperti store)
            $request->validate([
                'cart_data'     => 'required|json',
                'pelanggan_id'  => 'required|uuid|exists:pelanggans,id',
                'jumlah_bayar'  => 'required',
            ]);

            // 1. --- LOGIKA PEMBATALAN TRANSAKSI LAMA (Kembalikan Stok) ---
            foreach ($penjualan->detailPenjualans as $detail) {
                $produk = Produk::lockForUpdate()->find($detail->produk_id);
                if ($produk) {
                    $produk->stok_produk += $detail->qty; // Kembalikan stok lama
                    $produk->save();
                }
            }
            // Hapus detail lama
            DetailPenjualan::where('penjualan_id', $penjualan->id)->delete();

            // 2. --- LOGIKA TRANSAKSI BARU (Sama seperti logic di POSController@store) ---

            // Fungsi helper untuk membersihkan format uang (dari POSController)
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

            // Hitung ulang semua item dan cek stok
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

                // Cek Stok TERBARU
                if ($produk->stok_produk < $qty) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Stok tidak mencukupi untuk ' . $produk->nama_produk . '. Tersedia: ' . $produk->stok_produk . ', Diminta: ' . $qty)->withInput();
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
                    'produk_model'    => $produk, // Untuk update stok di bawah
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
            if ($jumlahBayar < $totalBayarComputed) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Jumlah bayar tidak mencukupi. Total yang harus dibayar: Rp ' . number_format($totalBayarComputed, 0, ',', '.'))
                    ->withInput();
            }

            $kembalian = round($jumlahBayar - $totalBayarComputed, 2);

            // 3. --- UPDATE PENJUALAN UTAMA ---
            $penjualan->update([
                'tanggal_penjualan' => now()->toDateString(), // Update tanggal hari ini? Atau biarkan tanggal lama? Asumsi: biarkan tanggal lama, tapi boleh diubah ke today()
                'total_harga'       => $subtotalAfterProductDiscounts,
                'diskon_percent'    => $diskonTransPercent,
                'diskon_nominal'    => $diskonTransNominal,
                'total_bayar'       => $totalBayarComputed,
                'jumlah_bayar'      => $jumlahBayar,
                'kembalian'         => $kembalian,
                'pelanggan_id'      => $request->pelanggan_id,
                'user_id'           => auth()->user()->id, // Kasir yang update
            ]);

            // 4. Simpan detail penjualan baru + update stok
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

                // Kurangi stok produk
                $produk = $item['produk_model'];
                $produk->stok_produk -= $item['qty'];
                $produk->save();
            }

            DB::commit();

            return redirect()->route('invoice.show', $penjualan->id)
                ->with('success', 'Transaksi berhasil diubah! Kode Transaksi: ' . $penjualan->kode_penjualan);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan server yang tidak diketahui.';
            Log::error('Invoice update error: ' . $errorMessage . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal mengubah transaksi. ' . $errorMessage)->withInput();
        }
    }

    public function destroy(Penjualan $penjualan)
    {
        if ($penjualan->status === 'Returned') {
            return redirect()->back()->with('error', 'Transaksi yang telah dikembalikan tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            // 1. Kembalikan Stok Produk
            foreach ($penjualan->detailPenjualans as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    $produk->stok_produk += $detail->qty;
                    $produk->save();
                }
            }

            // 2. Hapus Detail Penjualan
            DetailPenjualan::where('penjualan_id', $penjualan->id)->delete();

            // 3. Hapus Penjualan
            $penjualan->delete();

            DB::commit();

            return redirect()->route('invoice.index')->with('success', 'Transaksi Penjualan berhasil dihapus dan stok telah dikembalikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus transaksi. ' . $e->getMessage());
        }
    }
}
