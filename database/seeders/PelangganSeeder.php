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

        // Hapus data lama untuk menghindari duplikasi (opsional)
        // DB::table('pelanggans')->truncate();

        $faker = \Faker\Factory::create('id_ID');
        $numPelanggan = 50;
        $pelangganData = [];

        // Buat 50 data Pelanggan
        for ($i = 0; $i < $numPelanggan; $i++) {

            // Generate nama lengkap, lalu gabungkan dengan kode unik untuk email
            $namaLengkap = $faker->firstName() . ' ' . $faker->lastName();
            $emailBase = strtolower(str_replace(' ', '.', $namaLengkap));

            $pelangganData[] = [
                // Menggunakan Str::uuid() karena id adalah primary key bertipe UUID
                'id' => Str::uuid()->toString(),
                'nama_pelanggan' => $namaLengkap,
                // Pastikan email unik, tambahkan nomor jika perlu
                'email' => $faker->unique()->safeEmail(),
                'telp' => $faker->phoneNumber(),
                'photo_pelanggan' => null, // Biarkan kosong atau tambahkan path dummy jika perlu
                'created_at' => Carbon::now()->subDays($numPelanggan - $i),
                'updated_at' => Carbon::now()->subDays($numPelanggan - $i),
            ];
        }

        // Masukkan data dalam batch
        DB::table('pelanggans')->insert($pelangganData);

        $this->command->info('50 data pelanggan berhasil dibuat!');
    }
}
