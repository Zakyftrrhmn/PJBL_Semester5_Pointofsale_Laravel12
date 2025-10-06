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
    public function run(): void
    {
        $kategori = Kategori::where('nama_kategori', 'Alat Laboratorium')->first() ?? Kategori::first();
        $merek = Merek::where('nama_merek', 'Olympus')->first() ?? Merek::first();
        $satuan = Satuan::where('nama_satuan', 'Pieces')->first() ?? Satuan::first();

        $produkList = [
            // 🔬 ALAT LABORATORIUM KIMIA & BIOLOGI
            ['PRD0001', 'Mikroskop Biologi', 10, 1500000, 2000000, 'Mikroskop pembelajaran biologi sekolah.'],
            ['PRD0002', 'Tabung Reaksi', 100, 5000, 8000, 'Tabung kaca untuk percobaan kimia.'],
            ['PRD0003', 'Gelas Ukur 100ml', 40, 15000, 25000, 'Gelas ukur kaca borosilikat 100ml.'],
            ['PRD0004', 'Erlenmeyer 250ml', 30, 18000, 30000, 'Labu erlenmeyer 250ml.'],
            ['PRD0005', 'Beaker Glass 500ml', 35, 20000, 35000, 'Beaker glass 500ml untuk mencampur larutan.'],
            ['PRD0006', 'Buret 50ml', 25, 40000, 60000, 'Buret kaca untuk titrasi.'],
            ['PRD0007', 'Pipet Volume 10ml', 50, 7000, 12000, 'Pipet volumetrik 10ml.'],
            ['PRD0008', 'Kertas Saring', 200, 1000, 2000, 'Kertas saring laboratorium ukuran 90mm.'],
            ['PRD0009', 'Timbangan Digital 500g', 8, 400000, 550000, 'Timbangan presisi untuk laboratorium.'],
            ['PRD0010', 'pH Meter Digital', 12, 250000, 350000, 'Alat pengukur pH digital portabel.'],
            ['PRD0011', 'Spatula Logam', 100, 5000, 8000, 'Spatula stainless steel laboratorium.'],
            ['PRD0012', 'Kaca Arloji', 100, 3000, 5000, 'Kaca arloji diameter 80mm.'],
            ['PRD0013', 'Bunsen Burner', 15, 80000, 120000, 'Pembakar bunsen untuk pemanasan bahan.'],
            ['PRD0014', 'Termometer Laboratorium', 20, 25000, 40000, 'Termometer kaca 0–100°C.'],
            ['PRD0015', 'Cawan Petri', 80, 3000, 6000, 'Cawan kaca untuk kultur mikroorganisme.'],
            ['PRD0016', 'Labu Ukur 100ml', 35, 15000, 28000, 'Labu ukur kaca borosilikat 100ml.'],
            ['PRD0017', 'Corong Kaca', 50, 5000, 9000, 'Corong kaca 75mm.'],
            ['PRD0018', 'Hot Plate Magnetic Stirrer', 6, 900000, 1200000, 'Alat pemanas dan pengaduk magnetik.'],
            ['PRD0019', 'Pipet Mikro Adjustable 100-1000µL', 10, 500000, 750000, 'Pipet mikro volume adjustable.'],
            ['PRD0020', 'Rak Tabung Reaksi', 20, 25000, 45000, 'Rak kayu untuk tabung reaksi.'],

            // ⚡ ALAT FISIKA (OPTIK, LISTRIK, MEKANIKA)
            ['PRD0021', 'Jangka Sorong', 15, 45000, 75000, 'Alat ukur panjang presisi 0.02mm.'],
            ['PRD0022', 'Mikrometer Sekrup', 10, 80000, 120000, 'Alat ukur ketebalan dengan akurasi tinggi.'],
            ['PRD0023', 'Multimeter Analog', 20, 70000, 100000, 'Alat ukur tegangan dan arus listrik.'],
            ['PRD0024', 'Kumparan & Magnet Batang', 25, 20000, 35000, 'Set alat listrik dan magnet.'],
            ['PRD0025', 'Lensa Cembung & Cekung', 30, 15000, 30000, 'Lensa untuk percobaan optik.'],
            ['PRD0026', 'Prisma Kaca', 20, 25000, 40000, 'Prisma segitiga kaca untuk eksperimen cahaya.'],
            ['PRD0027', 'Cermin Datar dan Cembung', 30, 10000, 18000, 'Cermin optik untuk pembelajaran.'],
            ['PRD0028', 'Model Rangkaian Listrik Sederhana', 20, 80000, 120000, 'Model dasar arus listrik DC.'],
            ['PRD0029', 'Papan Optik', 10, 120000, 180000, 'Set papan optik lengkap dengan sumber cahaya.'],
            ['PRD0030', 'Bandul Matematik', 25, 15000, 25000, 'Bandul untuk percobaan gerak harmonik.'],

            // 🧠 MODEL & ALAT PERAGA EDUKATIF
            ['PRD0031', 'Model Torso Manusia 45cm', 5, 800000, 1200000, 'Model anatomi torso manusia lengkap organ.'],
            ['PRD0032', 'Model Sistem Pencernaan', 5, 500000, 800000, 'Model anatomi pencernaan manusia.'],
            ['PRD0033', 'Model Jantung Manusia', 8, 400000, 650000, 'Model anatomi jantung ukuran 2x.'],
            ['PRD0034', 'Model Otak Manusia', 6, 450000, 700000, 'Model otak 8 bagian dapat dibongkar.'],
            ['PRD0035', 'Model Tumbuhan Dikotil & Monokotil', 10, 150000, 250000, 'Model struktur batang tumbuhan.'],
            ['PRD0036', 'Model Sel Hewan & Tumbuhan', 10, 200000, 350000, 'Model 3D sel hewan dan tumbuhan.'],
            ['PRD0037', 'Model Rangka Manusia 85cm', 4, 900000, 1400000, 'Model kerangka manusia mini.'],
            ['PRD0038', 'Model Planet Tata Surya', 8, 300000, 500000, 'Model rotasi dan revolusi planet.'],
            ['PRD0039', 'Globe Dunia 30cm', 15, 180000, 300000, 'Globe peta dunia ukuran sedang.'],
            ['PRD0040', 'Peta Indonesia Dinding', 20, 50000, 85000, 'Peta besar Indonesia laminasi.'],

            // 🧰 PAKET KIT PENDIDIKAN
            ['PRD0041', 'KIT IPA SD', 5, 1200000, 1800000, 'Paket alat peraga IPA lengkap untuk SD.'],
            ['PRD0042', 'KIT Fisika SMP', 5, 1500000, 2200000, 'Paket alat peraga fisika SMP.'],
            ['PRD0043', 'KIT Biologi SMA', 5, 1800000, 2500000, 'Paket alat peraga biologi SMA.'],
            ['PRD0044', 'KIT Listrik & Magnet SMA', 5, 2000000, 2800000, 'Paket eksperimen listrik dan magnet.'],
            ['PRD0045', 'KIT Optik dan Cahaya', 5, 1600000, 2300000, 'Paket alat eksperimen optika.'],
            ['PRD0046', 'KIT Mekanika Dasar', 5, 1500000, 2100000, 'Paket alat pembelajaran gaya dan gerak.'],
            ['PRD0047', 'KIT Kimia Dasar', 5, 1700000, 2400000, 'Paket alat kimia untuk praktikum dasar.'],
            ['PRD0048', 'KIT Anatomi Manusia', 5, 2000000, 3000000, 'Paket model anatomi lengkap.'],
            ['PRD0049', 'KIT Ekosistem & Lingkungan', 5, 1200000, 1800000, 'Paket alat peraga lingkungan & biologi.'],
            ['PRD0050', 'KIT Energi dan Perubahan', 5, 1400000, 2100000, 'Paket alat demonstrasi energi.'],
        ];

        foreach ($produkList as $p) {
            Produk::create([
                'id' => Str::uuid(),
                'kode_produk' => $p[0],
                'nama_produk' => $p[1],
                'stok_produk' => rand(5, 100),
                'pengingat_stok' => 5,
                'harga_beli' => $p[3],
                'harga_jual' => $p[4],
                'deskripsi_produk' => $p[5],
                'is_active' => 'active',
                'kategori_id' => $kategori?->id,
                'merek_id' => $merek?->id,
                'satuan_id' => $satuan?->id,
            ]);
        }
    }
}
