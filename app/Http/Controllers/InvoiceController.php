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
     * Cetak invoice tanpa diskon
     */
    public function printNoDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
        $isDiscountApplied = false;

        $subTotalAwal = $penjualan->detailPenjualans->sum(fn($d) => $d->qty * $d->harga_satuan);
        $totalTanpaDiskon = $subTotalAwal;
        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalTanpaDiskon;

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalTanpaDiskon', 'bayar', 'kembalian');

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, 680, 400], 'portrait') // ukuran ±24cm x 14cm (half continuous form)
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-TanpaDiskon.pdf');
    }

    /**
     * Cetak invoice dengan diskon
     */
    public function printWithDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
        $isDiscountApplied = true;

        $subTotalAwal = $penjualan->detailPenjualans->sum(fn($d) => $d->qty * $d->harga_satuan);
        $totalDenganDiskon = $penjualan->total_bayar;
        $bayar = $penjualan->jumlah_bayar;
        $kembalian = $bayar - $totalDenganDiskon;

        $data = compact('penjualan', 'isDiscountApplied', 'subTotalAwal', 'totalDenganDiskon', 'bayar', 'kembalian');

        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper([0, 0, 680, 400], 'portrait') // ukuran ±24cm x 14cm (half continuous form)
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
    }
}
