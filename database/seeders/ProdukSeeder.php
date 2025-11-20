<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Kategori::where('nama_kategori', 'Alat Peraga Edukasi')->first() ?? Kategori::first();

        $kategoriId = $kategori?->id;

        // === Daftar Produk Berdasarkan Data Shopee ===
        $produkList = [
            ['IntiPeragaMandiri - TORSO LAKI-LAKI - Alat Peraga Edukasi Sekolah', 755000],
            ['Educational Block Bunga', 94500],
            ['IPMToys - Puzzle Chunky Animal / Mainan Edukasi', 55000],
            ['KOLEKSI BATUAN SEDIMEN INDONESIA', 110000],
            ['IntiPeragaMandiri - Manekin Torso Model Kepala Dan Otak', 260000],
            ['IPMToys - Mini Town City Centre /Construction / Transportasi Kayu', 90000],
            ['IPMToys - Muwanzi City Traffic Blocks 62 Pcs', 225000],
            ['Manekin Torso Model Hidung', 295000],
            ['Torso Manekin Model Jenazah', 1500000],
            ['Gelas ukur 100 ml Pyrex', 85800],
            ['Puzzle Kayu Huruf Hijaiyah Knob', 42000],
            ['IPMToys - Sudoku Kayu / Number Is Alone', 98000],
            ['IPMToys - Magnetic Board / Puzzle Magnet Kitchen', 115000],
            ['Manekin Torso Model Sapi', 495000],
            ['Mikroskop 500X Manual Sekolah', 1350000],
            ['Manekin Torso Model Paru-Paru', 286000],
            ['Manekin Torso Model Kulit', 286000],
            ['Torso Model Ginjal', 260000],
            ['Peta Lipat Benua / Negara / Provinsi / Kota', 29000],
            ['IPMToys - Kulintang Funny Xylophone', 35000],
            ['Torso Model Kulit', 260000],
            ['Torso Anatomi Manusia Wanita Uk Mini', 450000],
            ['Catur Besar', 47000],
            ['Globe Dunia Diameter 30cm', 415000],
            ['Peta Bingkai Benua / Negara / Pulau / Kota', 130000],
            ['Balok Piramida Huruf Abjad', 100000],
            ['Mini Magnetic Puzzle Hewan', 17000],
            ['Papan Tulis 2 Sisi Magnetic Alphabet', 77000],
            ['Mainan Edukasi Balok Kombinasi 42 pcs', 157500],
            ['Lilin Mainan Plastisin', 22500],
            ['Poster Sistem Saraf Manusia', 90000],
            ['Statif Batang dan Alas', 83000],
            ['Playmat Bright Crown', 200000],
            ['Lego Mr Block 266 pcs', 165000],
            ['Miniatur Hewan Laut', 52000],
            ['Lego Bongkar Pasang Mr Block 456 pcs', 295000],
            ['Torso Anatomi Dwifungsi Besar', 850000],
            ['Intelligent Building Block', 38000],
            ['Multifunctional Abacus Drawing Board', 148000],
            ['Mengenal Bentuk Basic Shape', 75500],
            ['Game Cube Puzzle / Tetris', 83000],
            ['Magnetic Letters and Numbers', 25000],
            ['Torso Kerangka Manusia Besar', 870000],
            ['Puzzle Ball Shape', 40000],
            ['Torso Anatomi Wanita Uk Besar', 720000],
            ['Pasir Kinetik Magic Sand 300g', 45000],
            ['Multifungsional Learning Box', 100000],
            ['Mainan Menghitung Sempoa Abacus', 95000],
            ['Cutting Fruit Board', 45000],
            ['Meronce Warna dan Bentuk', 110000],
            ['Sliding Puzzle Geser', 45000],
            ['Knowledge Classification Box', 135000],
            ['Pompa Tangan Bestway', 85000],
            ['Bola Kaki Mikasa', 150000],
            ['Meronce Huruf dan Angka Kayu', 135000],
            ['Mini Chunky Jigsaw Puzzle Animal', 8000],
            ['Wraps The Box', 145000],
            ['Puzzle Chunky Muwanzi', 76000],
            ['Basic Shape 4/6', 90000],
            ['Puzzle Buah Potong Sayur', 40000],
            ['Rainbow Stacker', 55000],
            ['Shape Intelligence Box', 58500],
            ['Balok Huruf dan Angka', 80000],
            ['Lego DIY Blocks Pipa', 99750],
            ['Kolam Renang Bestway 51008', 128000],
            ['Bowling Besar', 100000],
            ['3 in 1 Learning Numbers Shapes Colors', 152000],
            ['Miniatur Mobil Kayu', 125000],
            ['Magnetic Drawing Board Kodok', 32000],
            ['Puzzle Kayu Huruf dan Angka Timbul', 65000],
            ['Pink Tower Blocks Tower', 120000],
            ['Sliding Car Mobil Luncur', 125000],
            ['Carta Skema Gunung Api', 90000],
            ['Kereta Api Angka Digital Train', 131000],
            ['Educational Building Blocks Toys Meronce Huruf', 99750],
            ['Balok Kayu Puzzle Menara Kunci 4', 169575],
            ['Alat Bedah Hewan', 196900],
            ['Garpu Tala Set 4', 207900],
            ['Vernier Caliper 150mm', 150700],
            ['Educational Building Block Pipe', 99750],
            ['Mainan Buah Potong Box Kayu', 89000],
            ['Balok Natural 48 pcs', 220000],
            ['ABC Wood Blocks 27 pcs', 80000],
            ['Gelas Ukur 1000 ml Pyrex', 354100],
            ['Gelas Ukur 2000 ml Kaca', 669900],
            ['Carta Poster Gambar Kromosom', 90000],
            ['Carta Poster Gambar Ekskresi Manusia', 90000],
            ['Thermometer Dry And Wet', 85000],
            ['Torso Model Jantung', 286000],
            ['Mainan Smart Block 350 pcs', 135000],
            ['Atlas Indonesia dan Dunia Besar', 40700],
            ['Carta Poster Gambar Pohon Evolusi', 90000],
            ['Manekin Torso Model Gigi', 286000],
            ['Carta Poster Gambar Metamorfosis', 90000],
            ['Gelas Ukur 500 ml', 236500],
            ['Gelas Ukur 25 ml Pyrex', 57200],
            ['Carta Poster Tahapan Pertumbuhan Manusia', 90000],
            ['Carta Poster Molekul ADN dan ARN', 90000],
            ['Mainan Menjahit Buah Hedgehog', 155000],
            ['Gelas Ukur Set 10–100 ml', 71000],
            ['Balok Huruf Hewan 36 pcs', 200000],
            ['Torso Model Perkembangan Janin', 455000],
            ['Carta Poster Bumi dan Bulan', 90000],
            ['Manekin Torso Model Jakun', 286000],
            ['Torso Model Dikotil', 286000],
            ['DIY Gliding Car', 168000],
            ['Magnet U besar 8cm', 156200],
            ['Timbangan Emas Digital Pocket', 427900],
            ['Kit IPA SD Sains 130 Item', 2600000],
            ['Carta Poster Hereditas Mendel', 90000],
            ['Manekin Torso Model Putik', 286000],
            ['Fun Doh Si Ompong', 60000],
            ['Manekin Torso Model Harimau', 462000],
            ['Magnet Alnico', 288200],
            ['Kit Listrik dan Magnet SMP', 2970000],
            ['Kertas Lakmus pH 0-14', 220000],
        ];

        $i = 1;
        foreach ($produkList as [$nama, $hargaJual]) {
            $kode = 'PRD' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $hargaBeli = (int)($hargaJual * 0.8);
            $stok = rand(10, 50);
            $deskripsi = "Produk edukatif dan alat peraga: {$nama}. Cocok untuk sekolah, laboratorium, dan pembelajaran interaktif.";

            Produk::create([
                'id' => Str::uuid(),
                'kode_produk' => $kode,
                'nama_produk' => $nama,
                'stok_produk' => $stok,
                'pengingat_stok' => rand(5, 10),
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'deskripsi_produk' => $deskripsi,
                'is_active' => 'active',
                'kategori_id' => $kategoriId,
            ]);
            $i++;
        }

        echo "✅ Seeder produk selesai! Total produk: " . count($produkList) . "\n";
    }
}
