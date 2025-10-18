<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pages;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pages::create([
            'nama_toko'   => null,
            'nama_pemilik' => null,
            'logo_sidebar' => null,
            'logo_sidebar2' => null,
            'logo_login' => null,
            'favicon' => null,

            'jalan'      => null,
            'kelurahan'  => null,
            'kecamatan'  => null,
            'kota'       => null,
            'provinsi'   => null,
            'kode_pos'   => null,

            'telepon'    => null,
            'telepon2'   => null,
            'email'      => null,
        ]);
    }
}
