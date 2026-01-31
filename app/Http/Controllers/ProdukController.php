<?php

namespace App\Http\Controllers;

use App\Exports\ProdukExport;
use App\Models\Produk;
use App\Models\Kategori;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProdukController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:produk.index')->only('index', 'show');
        $this->middleware('permission:produk.create')->only('create', 'store');
        $this->middleware('permission:produk.edit')->only('edit', 'update');
        $this->middleware('permission:produk.destroy')->only('destroy');
        $this->middleware('permission:produk.export')->only('exportExcel', 'exportPDF');
    }

    public function index(Request $request)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        $produks = Produk::with(['kategori'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_produk', 'like', "%{$search}%")
                        ->orWhere('kode_produk', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori_id, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.produk.index', compact('produks', 'kategoris'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('pages.produk.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|string|max:50|unique:produks,kode_produk', // Tambahkan ini
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
            'kategori_id' => 'required|exists:kategoris,id',
            'photo_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo_produk')) {
            $data['photo_produk'] = $request->file('photo_produk')->store('produk', 'public');
        }

        Produk::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function show($id)
    {
        $produk = Produk::with(['kategori'])->findOrFail($id);

        return view('pages.produk.show', compact('produk'));
    }

    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('pages.produk.edit', compact('produk', 'kategoris'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'kode_produk'      => 'required|string|max:50|unique:produks,kode_produk,' . $id,
            'nama_produk'      => 'required|string|max:255',
            'stok_produk'      => 'required|integer|min:0',
            'pengingat_stok'   => 'required|integer|min:0',
            'harga_beli'       => 'required|integer|min:0',
            'harga_jual'       => 'required|integer|gt:harga_beli',
            'kategori_id'      => 'required|exists:kategoris,id',
            'is_active'        => 'required|in:active,non_active',
            'deskripsi_produk' => 'nullable|string',
            'photo_produk'     => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        if ($request->hasFile('photo_produk')) {
            if ($produk->photo_produk && Storage::exists('public/' . $produk->photo_produk)) {
                Storage::delete('public/' . $produk->photo_produk);
            }

            $path = $request->file('photo_produk')->store('produk', 'public');
            $produk->photo_produk = $path;
        }

        $produk->kode_produk      = $request->kode_produk;
        $produk->nama_produk      = $request->nama_produk;
        $produk->stok_produk      = $request->stok_produk;
        $produk->pengingat_stok   = $request->pengingat_stok;
        $produk->harga_beli       = $request->harga_beli;
        $produk->harga_jual       = $request->harga_jual;
        $produk->kategori_id      = $request->kategori_id;
        $produk->is_active        = $request->is_active;
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
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $produks = Produk::with(['kategori'])->get();
        $pdf = Pdf::loadView('pages.produk.pdf', compact('produks'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('produk.pdf');
    }


    // Tambahkan di ProdukController.php

    public function getPrefixes()
    {
        // Mengambil semua kode_produk, lalu ambil bagian hurufnya saja secara unik
        $produks = \App\Models\Produk::select('kode_produk')->get();

        $prefixes = $produks->map(function ($item) {
            // Mengambil karakter non-angka di awal string (Prefix)
            preg_match('/^[A-Z]+/', $item->kode_produk, $matches);
            return $matches[0] ?? null;
        })->filter()->unique()->values();

        return response()->json($prefixes);
    }

    public function generateKode(Request $request)
    {
        $prefix = strtoupper($request->prefix);

        // Cari produk terakhir dengan prefix tersebut
        // Gunakan regex database atau LIKE
        $latest = \App\Models\Produk::where('kode_produk', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(kode_produk) DESC')
            ->orderBy('kode_produk', 'desc')
            ->first();

        if ($latest) {
            // Ambil angka setelah prefix
            $currentNumber = str_replace($prefix, '', $latest->kode_produk);
            $nextNumber = intval($currentNumber) + 1;
        } else {
            $nextNumber = 1;
        }

        $newCode = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return response()->json(['kode' => $newCode]);
    }
}
