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
            // Informasi Utama Toko
            'nama_toko'     => 'Inti Peraga Mandiri',
            'nama_pemilik'  => 'Syarifah Fauziah',

            'logo_sidebar'  => null,
            'logo_sidebar2' => null,
            'logo_login'    => null,
            'favicon'       => null,

            'jalan'         => 'Jl. Jend. Ahmad Yani No.157 (Samping gg Arridha)',
            'kelurahan'     => 'Tanah Datar',
            'kecamatan'     => 'Pekanbaru Kota',
            'kota'          => 'Pekanbaru',
            'provinsi'      => 'Riau',
            'kode_pos'      => '28115',

            'telepon'       => '0813-7586-6604',
            'telepon2'      => null,
            'email'         => null,
        ]);
    }
}
