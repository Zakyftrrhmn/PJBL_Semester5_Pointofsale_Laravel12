<?php

namespace Database\Seeders;

use App\Models\Pemasok;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PemasokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pemasok::create([
            'nama_pemasok' => 'Pemasok 1',
            'telp' => '012345678',
            'email' => 'pemasok1@gmail.com',
            'alamat' => 'Jln Maharaja Indra',
            'photo_pemasok' => null,
        ]);
        Pemasok::create([
            'nama_pemasok' => 'Pemasok 2',
            'telp' => '012345678',
            'email' => 'pemasok2@gmail.com',
            'alamat' => 'Jln Maharaja Indra',
            'photo_pemasok' => null,
        ]);
        Pemasok::create([
            'nama_pemasok' => 'Pemasok 3',
            'telp' => '012345678',
            'email' => 'pemasok3@gmail.com',
            'alamat' => 'Jln Maharaja Indra',
            'photo_pemasok' => null,
        ]);
    }
}
