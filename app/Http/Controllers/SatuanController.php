<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{

    public function __construct()
    {
        // Menerapkan middleware untuk membatasi akses berdasarkan permission
        $this->middleware('permission:satuan.index')->only('index');
        $this->middleware('permission:satuan.create')->only(['create', 'store']);
        $this->middleware('permission:satuan.edit')->only(['edit', 'update']);
        $this->middleware('permission:satuan.destroy')->only('destroy');
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Jika ada pencarian
        $satuans = Satuan::when($request->search, function ($query, $search) {
            $query->where('nama_satuan', 'like', "%{$search}%");
        })->latest()->paginate(15)->withQueryString();

        return view('pages.satuan.index', compact('satuans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.satuan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:100|unique:satuans,nama_satuan',
        ]);

        Satuan::create([
            'nama_satuan' => $request->nama_satuan,
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('404');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Satuan $satuan)
    {
        return view('pages.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, Satuan $satuan)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:100|unique:satuans,nama_satuan,' . $satuan->id,
        ]);

        $satuan->update([
            'nama_satuan' => $request->nama_satuan,
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui!');
    }

    public function destroy(Satuan $satuan)
    {
        $satuan->delete();
        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus!');
    }
}
