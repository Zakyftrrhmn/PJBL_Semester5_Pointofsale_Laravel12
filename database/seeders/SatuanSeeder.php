<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Satuan::create(['nama_satuan' => 'Pieces']);
        Satuan::create(['nama_satuan' => 'Pack']);
        Satuan::create(['nama_satuan' => 'Rim',]);
        Satuan::create(['nama_satuan' => 'Lembar',]);
        Satuan::create(['nama_satuan' => 'Set',]);
        Satuan::create(['nama_satuan' => 'Karton',]);
    }
}
