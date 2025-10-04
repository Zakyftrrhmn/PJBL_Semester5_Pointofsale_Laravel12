<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PesananPembelianController extends Controller
{
    /**
     * Display a listing of the resource (Pesanan Pembelian).
     */
    public function index(Request $request)
    {
        $pembelians = Pembelian::with('pemasok')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_pembelian', 'like', "%{$search}%")
                    ->orWhereHas('pemasok', function ($q) use ($search) {
                        $q->where('nama_pemasok', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.pembelian.index', compact('pembelians'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembelian $pesanan_pembelian) // Menggunakan route model binding
    {
        $pembelian = $pesanan_pembelian->load('pemasok', 'detailPembelians.produk.satuan');
        return view('pages.pembelian.show', compact('pembelian'));
    }

    // Export PDF
    public function exportPDF(Pembelian $pembelian)
    {
        $pembelian->load('pemasok', 'detailPembelians.produk.satuan');
        $pdf = Pdf::loadView('pages.pembelian.pdf', compact('pembelian'));
        return $pdf->download('faktur-pembelian-' . $pembelian->kode_pembelian . '.pdf');
    }
}
