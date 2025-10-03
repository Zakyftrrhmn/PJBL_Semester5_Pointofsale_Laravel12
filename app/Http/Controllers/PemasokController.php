<?php

namespace App\Http\Controllers;

use App\Exports\PemasokExport;
use App\Models\Pemasok;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PemasokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pemasoks = Pemasok::when($request->search, function ($query, $search) {
            $query->where('nama_pemasok', 'like', "%{$search}%");
        })->latest()->paginate(15)->withQueryString();
        return view('pages.pemasok.index', compact('pemasoks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.pemasok.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'telp' => 'required|string|max:20',
            'email' => 'required|email|unique:pemasoks,email',
            'alamat' => 'required|max:150',
            'photo_pemasok' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_pemasok')) {
            $photoPath = $request->file('photo_pemasok')->store('pemasoks', 'public');
        }

        Pemasok::create([
            'nama_pemasok' => $request->nama_pemasok,
            'telp' => $request->telp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'photo_pemasok' => $photoPath,
        ]);

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pemasok $pemasok)
    {
        return view('pages.pemasok.edit', compact('pemasok'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pemasok $pemasok)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'telp' => 'required|string|max:20',
            'email' => 'required|email|unique:pemasoks,email,' . $pemasok->id,
            'alamat' => 'required|max:150',
            'photo_pemasok' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama_pemasok', 'telp', 'email']);

        if ($request->hasFile('photo_pemasok')) {
            // hapus foto lama jika ada
            if ($pemasok->photo_pemasok && Storage::disk('public')->exists($pemasok->photo_pemasok)) {
                Storage::disk('public')->delete($pemasok->photo_pemasok);
            }
            $data['photo_pemasok'] = $request->file('photo_pemasok')->store('pemasoks', 'public');
        }

        $pemasok->update($data);

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pemasok $pemasok)
    {
        if ($pemasok->photo_pemasok && Storage::disk('public')->exists($pemasok->photo_pemasok)) {
            Storage::disk('public')->delete($pemasok->photo_pemasok);
        }

        $pemasok->delete();

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil dihapus');
    }

    public function exportExcel()
    {
        return Excel::download(new PemasokExport, 'pemasok.xlsx');
    }

    public function exportPDF()
    {
        $pemasoks = Pemasok::all();
        $pdf = Pdf::loadView('pages.pemasok.pdf', compact('pemasoks'));
        return $pdf->download('pemasok.pdf');
    }
}
