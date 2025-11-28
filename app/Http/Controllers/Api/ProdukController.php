<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk; // Pastikan model Produk sudah di-import
use App\Models\Kategori; // Pastikan model Kategori sudah di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    /**
     * [READ] Menampilkan daftar semua produk dengan filter dan pagination.
     * Endpoint: GET /api/produks
     * Query Params: search, kategori_id, is_active
     */
    public function index(Request $request)
    {
        $produks = Produk::with(['kategori:id,nama_kategori'])
            ->when($request->search, function ($query, $search) {
                // Pencarian berdasarkan nama produk atau kode produk
                $query->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%");
            })
            ->when($request->kategori_id, function ($query, $kategoriId) {
                // Filter berdasarkan kategori
                $query->where('kategori_id', $kategoriId);
            })
            ->when($request->is_active, function ($query, $status) {
                // Filter berdasarkan status (active/non_active)
                $query->where('is_active', $status);
            })
            ->latest()
            ->paginate(15);

        // Tambahkan link penuh ke foto produk
        $produks->getCollection()->transform(function ($produk) {
            if ($produk->photo_produk) {
                $produk->photo_produk_url = Storage::url($produk->photo_produk);
            }
            return $produk;
        });

        return response()->json($produks);
    }

    /**
     * [CREATE] Menyimpan data produk baru.
     * Endpoint: POST /api/produks
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'stok_produk' => 'required|integer|min:0',
            'pengingat_stok' => 'nullable|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gt:harga_beli',
            'kategori_id' => 'required|exists:kategoris,id',
            'is_active' => ['required', Rule::in(['active', 'non_active'])],
            'deskripsi_produk' => 'nullable|string',
            'photo_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('photo_produk')) {
            $data['photo_produk'] = $request->file('photo_produk')->store('produk', 'public');
        } else {
            unset($data['photo_produk']);
        }

        $produk = Produk::create($data);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan!',
            'data' => $produk
        ], 201);
    }

    /**
     * [READ] Menampilkan detail data produk.
     * Endpoint: GET /api/produks/{produk}
     */
    public function show(Produk $produk)
    {
        $produk->load('kategori');

        // Tambahkan link penuh ke foto produk
        if ($produk->photo_produk) {
            $produk->photo_produk_url = Storage::url($produk->photo_produk);
        }

        return response()->json($produk);
    }

    /**
     * [UPDATE] Memperbarui data produk.
     * Endpoint: PUT/PATCH /api/produks/{produk}
     */
    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'stok_produk' => 'required|integer|min:0',
            'pengingat_stok' => 'nullable|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gt:harga_beli',
            'kategori_id' => 'required|exists:kategoris,id',
            'is_active' => ['required', Rule::in(['active', 'non_active'])],
            'deskripsi_produk' => 'nullable|string',
            'photo_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $validated;

        // Logika update photo_produk
        if ($request->hasFile('photo_produk')) {
            // Hapus foto lama jika ada
            if ($produk->photo_produk && Storage::disk('public')->exists($produk->photo_produk)) {
                Storage::disk('public')->delete($produk->photo_produk);
            }
            $data['photo_produk'] = $request->file('photo_produk')->store('produk', 'public');
        } else {
            // Jika tidak ada file baru, hapus field ini agar tidak menimpa data yang sudah ada (hanya berlaku jika method yang dikirim adalah PATCH)
            // Namun, karena kita menerima data yang sudah divalidasi, kita asumsikan data yang dikirim adalah data yang utuh.
        }

        $produk->update($data);

        return response()->json([
            'message' => 'Produk berhasil diperbarui!',
            'data' => $produk
        ], 200);
    }

    /**
     * [DELETE] Menghapus data produk.
     * Endpoint: DELETE /api/produks/{produk}
     */
    public function destroy(Produk $produk)
    {
        // Logika hapus foto
        if ($produk->photo_produk && Storage::disk('public')->exists($produk->photo_produk)) {
            Storage::disk('public')->delete($produk->photo_produk);
        }

        $produk->delete();

        return response()->json(['message' => 'Produk berhasil dihapus!'], 200);
    }

    /**
     * [HELPER] Mengambil daftar kategori untuk filter/dropdown di Mobile
     * Endpoint: GET /api/produks/kategoris
     */
    public function getKategoris()
    {
        $kategoris = Kategori::select('id', 'nama_kategori')->orderBy('nama_kategori')->get();
        return response()->json($kategoris);
    }
}
