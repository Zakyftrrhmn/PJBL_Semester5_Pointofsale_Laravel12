<?php

namespace App\Http\Controllers;

use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use App\Models\Produk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BarcodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:barcode.index');
    }

    public function index()
    {
        $produks = Produk::all();
        return view('pages.barcode.index', compact('produks'));
    }

    public function generateBarcodes(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        $produks = Produk::whereIn('id', $request->produk_id)->get();

        $barcodeDataModal = [];
        foreach ($produks as $produk) {
            $qty = $request->jumlah[$produk->id] ?? 1;
            $barcodeDataModal[] = [
                'nama_produk' => $produk->nama_produk,
                'kode_produk' => $produk->kode_produk,
                'qty' => $qty,
                // Diperbesar: width factor ke 2, height ke 40
                'barcode_html' => DNS1D::getBarcodeHTML($produk->kode_produk, 'C128', 2, 45),
            ];
        }
        return view('pages.barcode.modal-content', ['barcodeData' => $barcodeDataModal]);
    }

    public function cetakPdf(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        $produks = Produk::whereIn('id', $request->produk_id)->get();
        $jumlahData = $request->jumlah;

        // Menggunakan setting paper A4 portrait
        $pdf = Pdf::loadView('pages.barcode.cetak-pdf', compact('produks', 'jumlahData'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('barcode-produk.pdf');
    }
}
