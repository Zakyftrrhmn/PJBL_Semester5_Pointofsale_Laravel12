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
     * Jalankan seed database.
     * Logika telah diubah untuk menghasilkan 1000 produk secara dinamis.
     */
    public function run(): void
    {
        // 1. Ambil ID Kategori, Merek, dan Satuan yang sudah ada
        // Menggunakan item pertama jika tidak ditemukan yang spesifik.
        $kategori = Kategori::where('nama_kategori', 'Alat Laboratorium')->first() ?? Kategori::first();
        $merek = Merek::where('nama_merek', 'Olympus')->first() ?? Merek::first();
        $satuan = Satuan::where('nama_satuan', 'Pieces')->first() ?? Satuan::first();

        $kategoriId = $kategori?->id;
        $merekId = $merek?->id;
        $satuanId = $satuan?->id;

        // Daftar kategori produk umum untuk nama yang lebih variatif
        $productPrefixes = [
            'Alat Laboratorium',
            'Komponen Elektronik',
            'Peralatan Medis',
            'Bahan Habis Pakai',
            'Perkakas Tangan',
            'Aksesoris Komputer',
            'Alat Ukur Presisi',
            'Kit Edukasi',
            'Sparepart Mesin',
            'Cairan Kimia'
        ];

        $productCount = 1000;

        // Hapus data produk lama jika diperlukan (opsional, untuk memastikan database bersih)
        // Produk::truncate(); 

        echo "Memulai seeding $productCount produk...\n";

        // 2. Lakukan perulangan untuk membuat 1000 produk
        for ($i = 1; $i <= $productCount; $i++) {
            // Pembuatan data dinamis
            $prefixIndex = ($i - 1) % count($productPrefixes);
            $prefix = $productPrefixes[$prefixIndex];

            // Kode Produk: PRD0001 hingga PRD1000
            $kodeProduk = 'PRD' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $namaProduk = "$prefix $i";

            // Harga: Harga Beli acak dari 10.000 hingga 1.000.000
            $hargaBeli = rand(10000, 1000000);

            // Harga Jual: Markup 1.2x hingga 1.8x dari Harga Beli
            $markup = rand(120, 180) / 100;
            $hargaJual = (int)($hargaBeli * $markup);

            // Stok: Stok acak dari 10 hingga 300
            $stokProduk = rand(10, 300);
            $pengingatStok = rand(5, 20);

            $deskripsiProduk = "Deskripsi detail untuk produk $prefix dengan kode $kodeProduk. Item ini adalah produk ke-$i dari total $productCount.";

            Produk::create([
                'id' => Str::uuid(),
                'kode_produk' => $kodeProduk,
                'nama_produk' => $namaProduk,
                'stok_produk' => $stokProduk,
                'pengingat_stok' => $pengingatStok,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'deskripsi_produk' => $deskripsiProduk,
                'is_active' => 'active',
                'kategori_id' => $kategoriId,
                'merek_id' => $merekId,
                'satuan_id' => $satuanId,
            ]);
        }

        echo "Seeding $productCount produk berhasil diselesaikan.\n";
    }
}
