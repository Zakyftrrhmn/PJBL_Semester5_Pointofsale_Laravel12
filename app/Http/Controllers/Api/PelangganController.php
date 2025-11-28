<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan; // Pastikan model Pelanggan sudah di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{

    public function index(Request $request)
    {
        $pelanggans = Pelanggan::whereRaw('LOWER(nama_pelanggan) != ?', ['umum']) // Kecualikan 'Umum'
            ->when($request->search, function ($query, $search) {
                $query->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('telp', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return response()->json($pelanggans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'telp'           => 'nullable|string|max:15|unique:pelanggans,telp',
            'email'          => 'nullable|email|max:100|unique:pelanggans,email',
            'photo_pelanggan' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $validated;
        if ($request->hasFile('photo_pelanggan')) {
            $data['photo_pelanggan'] = $request->file('photo_pelanggan')->store('pelanggans', 'public');
        }

        $pelanggan = Pelanggan::create($data);

        return response()->json([
            'message' => 'Pelanggan berhasil ditambahkan!',
            'data' => $pelanggan
        ], 201);
    }


    public function show(Pelanggan $pelanggan)
    {
        if (strtolower($pelanggan->nama_pelanggan) === 'umum') {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        return response()->json($pelanggan);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        if (strtolower($pelanggan->nama_pelanggan) === 'umum') {
            return response()->json(['message' => 'Pelanggan "Umum" tidak dapat diubah.'], 403);
        }

        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'telp'           => ['nullable', 'string', 'max:15', Rule::unique('pelanggans', 'telp')->ignore($pelanggan->id)],
            'email'          => ['nullable', 'email', 'max:100', Rule::unique('pelanggans', 'email')->ignore($pelanggan->id)],
            'photo_pelanggan' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $validated;

        if ($request->hasFile('photo_pelanggan')) {
            if ($pelanggan->photo_pelanggan && Storage::disk('public')->exists($pelanggan->photo_pelanggan)) {
                Storage::disk('public')->delete($pelanggan->photo_pelanggan);
            }
            $data['photo_pelanggan'] = $request->file('photo_pelanggan')->store('pelanggans', 'public');
        }


        $pelanggan->update($data);

        return response()->json([
            'message' => 'Pelanggan berhasil diperbarui!',
            'data' => $pelanggan
        ], 200);
    }


    public function destroy(Pelanggan $pelanggan)
    {
        if (strtolower($pelanggan->nama_pelanggan) === 'umum') {
            return response()->json(['message' => 'Pelanggan "Umum" tidak dapat dihapus.'], 403);
        }

        if ($pelanggan->photo_pelanggan && Storage::disk('public')->exists($pelanggan->photo_pelanggan)) {
            Storage::disk('public')->delete($pelanggan->photo_pelanggan);
        }

        $pelanggan->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus!'], 200);
    }
}
