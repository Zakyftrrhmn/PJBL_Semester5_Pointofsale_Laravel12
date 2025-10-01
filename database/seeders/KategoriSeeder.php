<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Berdasarkan mata pelajaran
            ['nama_kategori' => 'IPA / Sains'],
            ['nama_kategori' => 'Matematika'],
            ['nama_kategori' => 'Bahasa'],
            ['nama_kategori' => 'IPS'],
            ['nama_kategori' => 'Seni & Budaya'],
            ['nama_kategori' => 'Teknologi / TIK'],
            ['nama_kategori' => 'Pendidikan Agama'],
            ['nama_kategori' => 'Bahasa Asing'],

            // Berdasarkan tingkat / level
            ['nama_kategori' => 'Sekolah Dasar'],
            ['nama_kategori' => 'Sekolah Menengah Pertama'],
            ['nama_kategori' => 'Sekolah Menengah Atas'],
            ['nama_kategori' => 'Pendidikan Anak Usia Dini'],

            // Berdasarkan bentuk alat peraga
            ['nama_kategori' => 'Alat Peraga Fisik / Model'],
            ['nama_kategori' => 'Alat Peraga Digital / Interaktif'],
            ['nama_kategori' => 'Alat Peraga Visual'],
            ['nama_kategori' => 'Alat Peraga Eksperimen'],
            ['nama_kategori' => 'Alat Peraga Elektromekanik'],

            // Berdasarkan fungsi / tujuan
            ['nama_kategori' => 'Demonstrasi / Penunjuk Konsep'],
            ['nama_kategori' => 'Praktik / Eksperimen Mandiri'],
            ['nama_kategori' => 'Simulasi'],
        ];

        foreach ($data as $kategori) {
            Kategori::create($kategori);
        }
    }
}
