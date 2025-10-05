<?php

namespace App\Http\Controllers;

use App\Exports\ProdukExport;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Merek;
use App\Models\Satuan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $mereks    = Merek::orderBy('nama_merek')->get();
        $satuans   = Satuan::orderBy('nama_satuan')->get();

        $produks = Produk::with(['kategori', 'merek', 'satuan'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_produk', 'like', "%{$search}%")
                        ->orWhere('kode_produk', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori_id, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->when($request->merek_id, function ($query, $merekId) {
                $query->where('merek_id', $merekId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.produk.index', compact('produks', 'kategoris', 'mereks', 'satuans'));
    }


    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $mereks    = Merek::all();
        $satuans   = Satuan::orderBy('nama_satuan', 'asc')->get();

        return view('pages.produk.create', compact('kategoris', 'mereks', 'satuans'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'stok_produk' => 'required|integer|min:0',
            'pengingat_stok'   => 'required|integer|min:0',
            'harga_beli' => 'required|integer|min:0',
            'harga_jual' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value <= $request->harga_beli) {
                        $fail('Harga jual harus lebih tinggi dari harga beli.');
                    }
                }
            ],
            'deskripsi_produk' => 'nullable|string|max:500',
            'is_active' => 'required|in:active,non_active',
            'satuan_id' => 'required|exists:satuans,id',
            'kategori_id' => 'required|exists:kategoris,id',
            'merek_id' => 'required|exists:mereks,id',
            'photo_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo_produk')) {
            $data['photo_produk'] = $request->file('photo_produk')->store('produk', 'public');
        }

        // Saat Produk::create($data) dipanggil, event 'saving' di model akan dieksekusi 
        // untuk memastikan 'is_active' sesuai dengan 'stok_produk'.
        Produk::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function show($id)
    {
        $produk = Produk::with(['kategori', 'merek', 'satuan'])->findOrFail($id);

        return view('pages.produk.show', compact('produk'));
    }


    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $mereks = Merek::orderBy('nama_merek')->get();
        $satuans = Satuan::orderBy('nama_satuan')->get();

        return view('pages.produk.edit', compact('produk', 'kategoris', 'mereks', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'stok_produk'      => 'required|integer|min:0',
            'pengingat_stok'   => 'required|integer|min:0',
            'harga_beli'       => 'required|integer|min:0',
            'harga_jual'       => 'required|integer|gt:harga_beli',
            'satuan_id'        => 'required|exists:satuans,id',
            'kategori_id'      => 'required|exists:kategoris,id',
            'merek_id'         => 'required|exists:mereks,id',
            'is_active'        => 'required|in:active,non_active',
            'deskripsi_produk' => 'nullable|string',
            'photo_produk'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update photo jika ada upload baru
        if ($request->hasFile('photo_produk')) {
            // Hapus foto lama
            if ($produk->photo_produk && Storage::exists('public/produk/' . $produk->photo_produk)) {
                Storage::delete('public/produk/' . $produk->photo_produk);
            }

            $filename = time() . '.' . $request->photo_produk->extension();
            $request->photo_produk->storeAs('public/produk', $filename);
            $produk->photo_produk = $filename;
        }

        // Update data lainnya
        $produk->nama_produk      = $request->nama_produk;
        $produk->stok_produk      = $request->stok_produk;
        $produk->pengingat_stok   = $request->pengingat_stok;
        $produk->harga_beli       = $request->harga_beli;
        $produk->harga_jual       = $request->harga_jual;
        $produk->satuan_id        = $request->satuan_id;
        $produk->kategori_id      = $request->kategori_id;
        $produk->merek_id         = $request->merek_id;
        $produk->is_active        = $request->is_active; // Nilai dari form tetap diset
        $produk->deskripsi_produk = $request->deskripsi_produk;

        $produk->save();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus');
    }
    public function exportExcel()
    {
        return Excel::download(new ProdukExport, 'produk.xlsx');
    }

    public function exportPDF()
    {
        $produks = Produk::with(['kategori', 'merek', 'satuan'])->get();
        $pdf = Pdf::loadView('pages.produk.pdf', compact('produks'));
        return $pdf->download('produk.pdf');
    }
}
