<?php

namespace App\Http\Controllers;

use App\Models\Pages;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Pages::first();
        return view('pages.pages.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_toko' => 'required|string|max:100',
            'nama_pemilik' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'telepon2' => 'nullable|string|max:20',
            'email' => 'required|email|max:100',
            'logo_sidebar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_sidebar2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_login' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:2048',
        ]);

        foreach (['logo_sidebar', 'logo_sidebar2', 'logo_login', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads/pages', 'public');
            }
        }

        Pages::create($validated);

        return redirect()->route('pages.index')->with('success', 'Data toko berhasil ditambahkan!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $page = Pages::findOrFail($id);

        $validated = $request->validate([
            'nama_toko' => 'required|string|max:100',
            'nama_pemilik' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'telepon2' => 'nullable|string|max:20',
            'email' => 'required|email|max:100',
            'logo_sidebar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_login' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:2048',
        ]);

        // Upload file baru jika ada
        foreach (['logo_sidebar', 'logo_sidebar2', 'logo_login', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads/pages', 'public');
            }
        }

        $page->update($validated);

        return redirect()->route('pages.index')->with('success', 'Data toko berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return view(404);
    }
}
