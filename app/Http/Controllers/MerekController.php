<?php

namespace App\Http\Controllers;

use App\Models\Merek;
use Illuminate\Http\Request;

class MerekController extends Controller
{
    public function __construct()
    {
        // Menerapkan middleware untuk membatasi akses berdasarkan permission
        $this->middleware('permission:merek.index')->only('index');
        $this->middleware('permission:merek.create')->only(['create', 'store']);
        $this->middleware('permission:merek.edit')->only(['edit', 'update']);
        $this->middleware('permission:merek.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Jika ada pencarian
        $mereks = Merek::when($request->search, function ($query, $search) {
            $query->where('nama_merek', 'like', "%{$search}%");
        })->where('is_default', false)->latest()->paginate(15)->withQueryString();

        return view('pages.merek.index', compact('mereks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.merek.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_merek' => 'required|string|max:100|unique:mereks,nama_merek',
        ]);

        Merek::create([
            'nama_merek' => $request->nama_merek,
        ]);

        return redirect()->route('merek.index')->with('success', 'Merek berhasil ditambahkan!');
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
    public function edit($id)
    {
        $merek = Merek::findOrFail($id);

        if ($merek->nama_merek === 'Tidak ada merek') {
            return redirect()->route('merek.index')->with('error', 'Merek default tidak boleh diubah.');
        }

        return view('pages.merek.edit', compact('merek'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Merek $merek)
    {
        $request->validate([
            'nama_merek' => 'required|string|max:100|unique:mereks,nama_merek,' . $merek->id,
        ]);

        $merek->update([
            'nama_merek' => $request->nama_merek,
        ]);

        return redirect()->route('merek.index')->with('success', 'Merek berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $merek = Merek::findOrFail($id);

        if ($merek->nama_merek === 'Tidak ada merek') {
            return redirect()->route('merek.index')->with('error', 'Merek default tidak boleh dihapus.');
        }

        $merek->delete();
        return redirect()->route('merek.index')->with('success', 'Merek berhasil dihapus!');
    }
}
