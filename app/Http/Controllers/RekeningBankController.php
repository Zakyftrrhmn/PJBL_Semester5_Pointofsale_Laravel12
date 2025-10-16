<?php

namespace App\Http\Controllers;

use App\Models\RekeningBank;
use Illuminate\Http\Request;

class RekeningBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rekeningBank.index')->only('index');
        $this->middleware('permission:rekeningBank.create')->only('create');
        $this->middleware('permission:rekeningBank.edit')->only('edit');
        $this->middleware('permission:rekeningBank.destroy')->only('destroy');
    }

    public function index(Request $request)
    {
        // Jika ada pencarian
        $rekeningBanks = RekeningBank::when($request->search, function ($query, $search) {
            $query
                ->where('nama_bank', 'like', "%{$search}%")
                ->orWhere('no_rekening', 'like', "%{$search}%")
                ->orWhere('nama_pemilik_rekening', 'like', "%{$search}%");
        })->latest()->paginate(15)->withQueryString();

        return view('pages.rekeningBank.index', compact('rekeningBanks'));
    }

    public function create()
    {
        return view('pages.rekeningBank.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bank' => 'required|string|max:50',
            'no_rekening' => 'required|numeric|digits_between:6,20|unique:rekening_banks,no_rekening',
            'nama_pemilik_rekening' => 'required|string|max:100',
        ]);

        RekeningBank::create($request->all());
        return redirect()->route('rekeningBank.index')->with('success', 'Selamat rekening bank berhasil ditambahkan!');
    }

    public function edit(RekeningBank $rekeningBank)
    {
        return view('pages.rekeningBank.edit', compact('rekeningBank'));
    }

    public function update(Request $request, RekeningBank $rekeningBank)
    {
        $request->validate([
            'nama_bank' => 'required|string|max:50',
            'no_rekening' => 'required|numeric|digits_between:6,20|unique:rekening_banks,no_rekening,' . $rekeningBank->id,
            'nama_pemilik_rekening' => 'required|string|max:100',
        ]);

        $rekeningBank->update($request->all());
        return redirect()->route('rekeningBank.index')->with('success', 'Rekening Bank berhasil di ubah!');
    }

    public function destroy(RekeningBank $rekeningBank)
    {
        $rekeningBank->delete();
        return redirect()->route('rekeningBank.index')->with('success', 'Rekenink bank berhasil di hapus!');
    }
}
