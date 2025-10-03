<?php

namespace Database\Seeders;

use App\Models\Merek;
use Illuminate\Database\Seeder;

class MerekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Merek::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'nama_merek' => 'Tidak ada merek',
            'is_default' => true,
        ]);

        $mereks = [
            ['nama_merek' => 'LEGO'],
            ['nama_merek' => 'Fisher-Price'],
            ['nama_merek' => 'Little Tikes'],
            ['nama_merek' => 'Melissa & Doug'],
            ['nama_merek' => 'Mainan Kayu Lokal'],
            ['nama_merek' => 'Alat Peraga PAUD (Lokal)'],
            ['nama_merek' => 'APE (Alat Peraga Edukasi)'],
            ['nama_merek' => 'Karakter Kartun'],
            ['nama_merek' => 'Superhero'],
            ['nama_merek' => 'Karakter Media Populer'],
        ];

        foreach ($mereks as $merek) {
            Merek::create($merek);
        }
    }
}
