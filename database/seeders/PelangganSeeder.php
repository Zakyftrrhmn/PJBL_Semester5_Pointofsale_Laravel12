<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pelanggan;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai pembuatan data pelanggan...');

        /**
         * ======================================================
         * 1. BUAT PELANGGAN UMUM (WAJIB ADA)
         * ======================================================
         */
        $pelangganUmum = Pelanggan::firstOrCreate(
            ['nama_pelanggan' => 'Umum'],
            [
                'id' => Str::uuid()->toString(),
                'email' => null,
                'telp' => null,
                'photo_pelanggan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✔ Pelanggan "Umum" siap digunakan (ID: ' . $pelangganUmum->id . ')');

        /**
         * ======================================================
         * 2. BUAT DATA PELANGGAN DUMMY
         * ======================================================
         */
        $faker = \Faker\Factory::create('id_ID');
        $numPelanggan = 50;
        $pelangganData = [];

        for ($i = 0; $i < $numPelanggan; $i++) {

            $namaLengkap = $faker->firstName() . ' ' . $faker->lastName();

            $pelangganData[] = [
                'id' => Str::uuid()->toString(),
                'nama_pelanggan' => $namaLengkap,
                'email' => $faker->unique()->safeEmail(),
                'telp' => $faker->phoneNumber(),
                'photo_pelanggan' => null,
                'created_at' => Carbon::now()->subDays($numPelanggan - $i),
                'updated_at' => Carbon::now()->subDays($numPelanggan - $i),
            ];
        }

        DB::table('pelanggans')->insert($pelangganData);

        $this->command->info('✔ ' . $numPelanggan . ' data pelanggan dummy berhasil dibuat!');
        $this->command->info('🎉 Seeder Pelanggan selesai tanpa error.');
    }
}
