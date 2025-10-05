<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\PelangganExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PelangganController extends Controller
{
    public function __construct()
    {
        // Menerapkan middleware untuk membatasi akses berdasarkan permission
        $this->middleware('permission:pelanggan.index')->only('index');
        $this->middleware('permission:pelanggan.create')->only(['create', 'store']);
        $this->middleware('permission:pelanggan.edit')->only(['edit', 'update']);
        $this->middleware('permission:pelanggan.destroy')->only('destroy');
        $this->middleware('permission:pelanggan.export')->only(['exportExcel', 'exportPDF']);
    }

    /**
     * Tampilkan daftar pelanggan
     */
    public function index(Request $request)
    {
        $pelanggans = Pelanggan::when($request->search, function ($query, $search) {
            $query->where('nama_pelanggan', 'like', "%{$search}%");
        })->latest()->paginate(15)->withQueryString();
        return view('pages.pelanggan.index', compact('pelanggans'));
    }

    /**
     * Form tambah pelanggan
     */
    public function create()
    {
        return view('pages.pelanggan.create');
    }

    /**
     * Simpan data pelanggan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'telp' => 'required|string|max:20',
            'email' => 'required|email|unique:pelanggans,email',
            'photo_pelanggan' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_pelanggan')) {
            $photoPath = $request->file('photo_pelanggan')->store('pelanggans', 'public');
        }

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'telp' => $request->telp,
            'email' => $request->email,
            'photo_pelanggan' => $photoPath,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Form edit pelanggan
     */
    public function edit(Pelanggan $pelanggan)
    {
        return view('pages.pelanggan.edit', compact('pelanggan'));
    }

    /**
     * Update data pelanggan
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'telp' => 'required|string|max:20',
            'email' => 'required|email|unique:pelanggans,email,' . $pelanggan->id,
            'photo_pelanggan' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama_pelanggan', 'telp', 'email']);

        if ($request->hasFile('photo_pelanggan')) {
            // hapus foto lama jika ada
            if ($pelanggan->photo_pelanggan && Storage::disk('public')->exists($pelanggan->photo_pelanggan)) {
                Storage::disk('public')->delete($pelanggan->photo_pelanggan);
            }
            $data['photo_pelanggan'] = $request->file('photo_pelanggan')->store('pelanggans', 'public');
        }

        $pelanggan->update($data);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui');
    }

    /**
     * Hapus data pelanggan
     */
    public function destroy(Pelanggan $pelanggan)
    {
        if ($pelanggan->photo_pelanggan && Storage::disk('public')->exists($pelanggan->photo_pelanggan)) {
            Storage::disk('public')->delete($pelanggan->photo_pelanggan);
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus');
    }

    public function exportExcel()
    {
        return Excel::download(new PelangganExport, 'pelanggan.xlsx');
    }

    public function exportPDF()
    {
        $pelanggans = Pelanggan::all();
        $pdf = Pdf::loadView('pages.pelanggan.pdf', compact('pelanggans'));
        return $pdf->download('pelanggan.pdf');
    }
}
