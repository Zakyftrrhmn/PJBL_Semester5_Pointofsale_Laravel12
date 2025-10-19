<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\Terbilang;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:invoice.index')->only('index', 'show');
        $this->middleware('can:invoice.export')->only('printNoDiscount', 'printWithDiscount');
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
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
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
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
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
            ->setPaper([0, 0, 680, 400], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'chroot' => public_path(),]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-TanpaDiskon.pdf');
    }

    /**
     * Cetak invoice DENGAN diskon.
     * Item: Subtotal (Diskon item diperhitungkan).
     * Transaksi: Diskon transaksi diperhitungkan.
     */
    public function printWithDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
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
            ->setPaper([0, 0, 680, 400], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'chroot' => public_path(),]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
    }
}