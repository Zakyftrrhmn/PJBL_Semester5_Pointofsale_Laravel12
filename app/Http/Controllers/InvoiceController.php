<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Asumsi menggunakan DomPDF seperti pada fitur ekspor lain

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
        $penjualans = Penjualan::with(['pelanggan', 'user'])
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
        // Load relasi yang diperlukan untuk tampilan detail
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');

        // Mengirim data ke view detail
        return view('pages.invoice.show', compact('penjualan'));
    }

    /**
     * Cetak Invoice (Struk) TANPA Diskon.
     */
    public function printNoDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
        $isDiscountApplied = false; // Flag untuk template print

        $data = compact('penjualan', 'isDiscountApplied');

        // Atur ukuran kertas kecil (struk kasir)
        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper('a7', 'portrait') // Ukuran kecil A7, bisa diatur [0, 0, 200, 300] untuk custom
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-TanpaDiskon.pdf');
    }

    /**
     * Cetak Invoice (Struk) DENGAN Diskon.
     */
    public function printWithDiscount(Penjualan $penjualan)
    {
        $penjualan->load('pelanggan', 'user', 'detailPenjualans.produk.satuan');
        $isDiscountApplied = true; // Flag untuk template print

        $data = compact('penjualan', 'isDiscountApplied');

        // Atur ukuran kertas kecil (struk kasir)
        $pdf = Pdf::loadView('pages.invoice.print-template', $data)
            ->setPaper('a7', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('Invoice-' . $penjualan->kode_penjualan . '-DenganDiskon.pdf');
    }
}
