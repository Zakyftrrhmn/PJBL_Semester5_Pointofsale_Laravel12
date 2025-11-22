<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KategoriSeeder::class,
            PemasokSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            PagesSeeder::class,
            PelangganSeeder::class,
            ProdukSeeder::class,
            // PembelianSeeder::class,
            PenjualanSeeder::class,
        ]);
    }
}
