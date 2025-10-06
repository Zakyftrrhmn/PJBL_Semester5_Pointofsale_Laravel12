<?php

namespace App\Http\Controllers;

use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use App\Models\Produk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BarcodeController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        return view('pages.barcode.index', compact('produks'));
    }

    public function generateBarcodes(Request $request)
    {
        // Validasi input
        $request->validate([
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        $produks = Produk::whereIn('id', $request->produk_id)->get();

        $barcodeDataModal = [];
        foreach ($produks as $produk) {
            if (!isset($barcodeDataModal[$produk->id])) {
                $qty = $request->jumlah[$produk->id] ?? 1; 
                $barcodeDataModal[$produk->id] = [
                    'nama_produk' => $produk->nama_produk,
                    'kode_produk' => $produk->kode_produk,
                    'qty' => $qty, 
                    'barcode_html' => DNS1D::getBarcodeHTML($produk->kode_produk, 'C128', 1.5, 30),
                ];
            }
        }
        return view('pages.barcode.modal-content', ['barcodeData' => array_values($barcodeDataModal)]);
    }

    public function cetakPdf(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        $produks = Produk::whereIn('id', $request->produk_id)->get();
        $jumlahData = $request->jumlah;

        $pdf = Pdf::loadView('pages.barcode.cetak-pdf', compact('produks', 'jumlahData'));

        return $pdf->stream('barcode.pdf');
    }
}
