<?php

namespace App\Http\Controllers;

use App\Models\Pages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PagesController extends Controller
{
    public function index()
    {
        $page = Pages::first();
        return view('pages.pages.index', compact('page'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_toko' => 'nullable|string|max:100',
            'nama_pemilik' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'telepon2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'logo_sidebar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_sidebar2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_login' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:2048',
        ]);

        // Simpan file upload jika ada
        foreach (['logo_sidebar', 'logo_sidebar2', 'logo_login', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads/pages', 'public');
            }
        }

        Pages::create($validated);

        return redirect()->route('pages.index')->with('success', 'Data toko berhasil ditambahkan!');
    }

    public function update(Request $request, string $id)
    {
        $page = Pages::findOrFail($id);

        $validated = $request->validate([
            'nama_toko' => 'nullable|string|max:100',
            'nama_pemilik' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'telepon2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'logo_sidebar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_sidebar2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_login' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:2048',
        ]);

        foreach (['logo_sidebar', 'logo_sidebar2', 'logo_login', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                // Hapus file lama jika ada
                if ($page->$field && Storage::disk('public')->exists($page->$field)) {
                    Storage::disk('public')->delete($page->$field);
                }

                // Upload baru
                $validated[$field] = $request->file($field)->store('uploads/pages', 'public');
            } else {
                // Pertahankan yang lama
                $validated[$field] = $page->$field;
            }
        }

        $page->update($validated);

        return redirect()->route('pages.index')->with('success', 'Data toko berhasil diperbarui!');
    }

    public function create()
    {
        abort(404);
    }
    public function show(string $id)
    {
        abort(404);
    }
    public function edit(string $id)
    {
        abort(404);
    }
    public function destroy(string $id)
    {
        abort(404);
    }
}
