<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Merek;
use App\Models\Satuan;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data referensi (kategori, merek, satuan)
        $kategori = Kategori::where('nama_kategori', 'Alat Laboratorium')->first() ?? Kategori::first();
        $merek = Merek::where('nama_merek', 'Olympus')->first() ?? Merek::first();
        $satuan = Satuan::where('nama_satuan', 'Pieces')->first() ?? Satuan::first();

        // Seed produk contoh
        Produk::create([
            'id' => Str::uuid(),
            'kode_produk' => 'PRD0001',
            'nama_produk' => 'Mikroskop Biologi',
            'stok_produk' => 10,
            'pengingat_stok' => 10,
            'harga_beli' => 1500000,
            'harga_jual' => 2000000,
            'deskripsi_produk' => 'Mikroskop cahaya untuk pembelajaran biologi.',
            'is_active' => 'active',
            'kategori_id' => $kategori?->id,
            'merek_id' => $merek?->id,
            'satuan_id' => $satuan?->id,
        ]);

        Produk::create([
            'id' => Str::uuid(),
            'kode_produk' => 'PRD0002',
            'nama_produk' => 'Tabung Reaksi',
            'stok_produk' => 100,
            'pengingat_stok' => 10,
            'harga_beli' => 5000,
            'harga_jual' => 8000,
            'deskripsi_produk' => 'Tabung kaca untuk percobaan laboratorium.',
            'is_active' => 'active',
            'kategori_id' => $kategori?->id,
            'merek_id' => $merek?->id,
            'satuan_id' => $satuan?->id,
        ]);
    }
}
