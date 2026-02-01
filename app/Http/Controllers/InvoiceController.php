<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:invoice.index')->only('index', 'show');
        $this->middleware('can:invoice.export')->only('printNoDiscount', 'printWithDiscount');
        $this->middleware('can:invoice.edit')->only('edit');
        $this->middleware('can:invoice.destroy')->only('destroy');
    }

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

    public function show(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');
        return view('pages.invoice.show', compact('penjualan'));
    }

    protected function calculateSubtotalMurni(Penjualan $penjualan)
    {
        return $penjualan->detailPenjualans->sum(fn($d) => $d->qty * $d->harga_satuan);
    }

    protected function calculateSubtotalAfterItemDiscount(Penjualan $penjualan)
    {
        return $penjualan->total_harga;
    }

    protected function calculateDynamicPaperHeight($itemCount, $hasDiscount = false)
    {
        $headerHeight = 150;
        $itemRowHeight = 20;
        $footerHeight = 180;
        $bufferSpace = 50;

        if ($hasDiscount) {
            $footerHeight += 20;
        }

        $calculatedHeight = $headerHeight + ($itemCount * $itemRowHeight) + $footerHeight + $bufferSpace;

        $minHeight = 500;
        $maxHeight = 1400;

        if ($calculatedHeight < $minHeight) {
            return $minHeight;
        } elseif ($calculatedHeight > $maxHeight) {
            return $maxHeight;
        }

        return $calculatedHeight;
    }

    public function printNoDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');

        $isDiscountApplied = false;
        $subTotalAwal = $this->calculateSubtotalMurni($penjualan);
        $totalFinal = $subTotalAwal;
        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalFinal;
        $item_total_type = 'MURNI';

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalFinal', 'bayar', 'kembalian', 'item_total_type');

        $itemCount = $penjualan->detailPenjualans->count();
        $paperWidth = 612;
        $paperHeight = $this->calculateDynamicPaperHeight($itemCount, false);

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, $paperWidth, $paperHeight], 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'defaultFont' => 'Courier New',
                'dpi' => 96,
                'enable_php' => false
            ]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-TanpaDiskon.pdf');
    }

    public function printWithDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk');

        $isDiscountApplied = true;
        $subTotalAwal = $this->calculateSubtotalAfterItemDiscount($penjualan);
        $totalFinal = $penjualan->total_bayar;
        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalFinal;
        $item_total_type = 'DISKON';

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalFinal', 'bayar', 'kembalian', 'item_total_type');

        $itemCount = $penjualan->detailPenjualans->count();
        $paperWidth = 612;
        $hasDiscountRow = $penjualan->diskon_nominal > 0;
        $paperHeight = $this->calculateDynamicPaperHeight($itemCount, $hasDiscountRow);

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, $paperWidth, $paperHeight], 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'defaultFont' => 'Courier New',
                'dpi' => 96,
                'enable_php' => false
            ]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
    }

    public function edit(Penjualan $penjualan)
    {
        if ($penjualan->status === 'Returned') {
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('error', 'Transaksi yang telah diretur tidak dapat diedit.');
        }

        $penjualan->load('detailPenjualans.produk');

        $pelangganUmum = \App\Models\Pelanggan::where('nama_pelanggan', 'Umum')->first();
        $pelanggans = \App\Models\Pelanggan::orderBy('nama_pelanggan')->get(['id', 'nama_pelanggan']);
        $produks = \App\Models\Produk::where('is_active', 'active')->orderBy('nama_produk')->paginate(30);

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

        // ✅ PERBAIKAN: Load diskon item dengan benar
        $initialCart = $penjualan->detailPenjualans->map(function ($detail) {
            return [
                'id' => $detail->produk_id,
                'nama_produk' => $detail->produk->nama_produk,
                'kode_produk' => $detail->produk->kode_produk,
                'photo_produk' => $detail->produk->photo_produk,
                'harga_satuan' => (float) $detail->harga_satuan,
                'stok_produk' => $detail->produk->stok_produk + $detail->qty,
                'qty' => (int) $detail->qty,
                'diskon_item_percent' => (float) $detail->diskon_percent,  // ✅ PERBAIKAN: nama field
                'diskon_item_nominal' => (float) $detail->diskon_nominal,  // ✅ PERBAIKAN: nama field
                'subtotal' => (float) $detail->subtotal,
            ];
        })->toArray();

        return view('pages.pos.index', compact('produks', 'produksForJs', 'pelanggans', 'pelangganUmum', 'penjualan'))
            ->with('isEditMode', true)
            ->with('initialCart', $initialCart)
            ->with('initialPelangganId', $penjualan->pelanggan_id)
            ->with('initialDiskonPercent', $penjualan->diskon_percent);
    }

    /**
     * ✅ UPDATE — Simpan perubahan DENGAN DISKON PER PRODUK
     */
    public function update(Request $request, Penjualan $penjualan)
    {
        if ($penjualan->status === 'Returned') {
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('error', 'Transaksi yang telah diretur tidak dapat diubah.');
        }

        DB::beginTransaction();
        try {
            $request->validate([
                'cart_data' => 'required|json',
                'pelanggan_id' => 'required|uuid|exists:pelanggans,id',
                'tanggal_penjualan' => 'required|date',
            ]);

            $penjualan->load('detailPenjualans');

            // Kembalikan stok lama
            foreach ($penjualan->detailPenjualans as $detail) {
                $produk = Produk::lockForUpdate()->find($detail->produk_id);
                if ($produk) {
                    $produk->stok_produk += $detail->qty;
                    $produk->save();
                }
            }

            // Hapus detail lama
            DetailPenjualan::where('penjualan_id', $penjualan->id)->delete();

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
                    return redirect()->back()->with('error', 'Produk tidak ditemukan')->withInput();
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
                    'produk_model' => $produk,
                ];
            }

            $diskonTransPercent = (float) ($request->diskon_percent ?? 0);
            if ($diskonTransPercent < 0) $diskonTransPercent = 0;
            if ($diskonTransPercent > 100) $diskonTransPercent = 100;

            $diskonTransNominal = round(($diskonTransPercent / 100) * $subtotalAfterProductDiscounts);
            $totalBayarComputed = $subtotalAfterProductDiscounts - $diskonTransNominal;
            if ($totalBayarComputed < 0) $totalBayarComputed = 0;

            $penjualan->update([
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

            // ✅ Simpan detail baru DENGAN DISKON ITEM + kurangi stok
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

                $produk = $item['produk_model'];
                $produk->stok_produk -= $item['qty'];
                $produk->save();
            }

            DB::commit();
            return redirect()->route('invoice.show', $penjualan->id)
                ->with('success', 'Transaksi berhasil diubah! Kode: ' . $penjualan->kode_penjualan);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengubah transaksi. ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Penjualan $penjualan)
    {
        if ($penjualan->status === 'Returned') {
            return redirect()->back()->with('error', 'Transaksi yang telah dikembalikan tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            foreach ($penjualan->detailPenjualans as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    $produk->stok_produk += $detail->qty;
                    $produk->save();
                }
            }

            DetailPenjualan::where('penjualan_id', $penjualan->id)->delete();
            $penjualan->delete();

            DB::commit();
            return redirect()->route('invoice.index')->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus transaksi. ' . $e->getMessage());
        }
    }
}
