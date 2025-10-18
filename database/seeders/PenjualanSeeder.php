<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produkIds = Produk::pluck('id')->toArray();
        $pelangganIds = Pelanggan::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        if (empty($produkIds) || empty($pelangganIds) || empty($userIds)) {
            $this->command->info('Pastikan tabel produks, pelanggans, dan users sudah terisi data.');
            return;
        }

        $faker = \Faker\Factory::create('id_ID');
        $numPenjualan = 500;
        $tanggalAwal = Carbon::now()->subMonths(6);

        // === Tambahan: Batas retur hanya 5 ===
        $maxRetur = 5;
        $returCount = 0;

        $this->command->info("Memulai pembuatan {$numPenjualan} data penjualan (maksimal {$maxRetur} retur)...");

        DB::beginTransaction();

        try {
            for ($i = 1; $i <= $numPenjualan; $i++) {
                $tanggalObj = $faker->dateTimeBetween($tanggalAwal, 'now');
                $tanggal = $tanggalObj->format('Y-m-d');
                $kodePenjualan = 'PJL' . $tanggalObj->format('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT);

                $pelangganId = $faker->randomElement($pelangganIds);
                $userId = $faker->randomElement($userIds);

                $totalHarga = 0;
                $detailsData = [];

                // === Detail Penjualan ===
                $numDetails = $faker->numberBetween(1, 5);
                $selectedProdukIds = $faker->randomElements($produkIds, $numDetails, false);

                foreach ($selectedProdukIds as $produkId) {
                    $produk = Produk::find($produkId);
                    if (!$produk) continue;

                    $hargaSatuan = $produk->harga_jual;
                    $qty = $faker->numberBetween(1, 10);
                    $subtotal = $hargaSatuan * $qty;
                    $totalHarga += $subtotal;

                    $detailsData[] = [
                        'produk_id' => $produkId,
                        'qty' => $qty,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                        'created_at' => $tanggalObj,
                        'updated_at' => $tanggalObj,
                    ];
                }

                if (empty($detailsData)) continue;

                $diskon = $faker->boolean(20) ? (int)($totalHarga * 0.05) : 0;
                $totalBayar = $totalHarga - $diskon;
                $jumlahBayar = $totalBayar + $faker->numberBetween(0, 50000);
                $kembalian = $jumlahBayar - $totalBayar;

                $penjualan = Penjualan::create([
                    'kode_penjualan' => $kodePenjualan,
                    'tanggal_penjualan' => $tanggal,
                    'total_harga' => $totalHarga,
                    'diskon' => $diskon,
                    'total_bayar' => $totalBayar,
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'pelanggan_id' => $pelangganId,
                    'user_id' => $userId,
                    'created_at' => $tanggalObj,
                    'updated_at' => $tanggalObj,
                ]);

                foreach ($detailsData as $detail) {
                    $detail['penjualan_id'] = $penjualan->id;
                    DetailPenjualan::create($detail);
                }

                // === Buat retur hanya untuk 5 data pertama ===
                if ($returCount < $maxRetur) {
                    $this->createRetur($penjualan->id, $tanggalObj, $userId, $detailsData);
                    $returCount++;
                }

                if ($i % 100 === 0) {
                    $this->command->info("Progress: {$i} data penjualan berhasil dibuat...");
                }
            }

            DB::commit();
            $this->command->info("Selesai! {$numPenjualan} Penjualan dibuat dengan {$returCount} Retur saja.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal melakukan seeding Penjualan: ' . $e->getMessage());
            $this->command->error('Pastikan data relasi (Pelanggan, Produk, User) sudah terisi.');
        }
    }

    /**
     * Buat data retur untuk penjualan.
     */
    protected function createRetur($penjualanId, $tanggalPenjualanObj, $userId, $detailsData)
    {
        $faker = \Faker\Factory::create('id_ID');
        $tanggalRetur = $faker->dateTimeBetween($tanggalPenjualanObj, Carbon::now())->format('Y-m-d');

        $detailToRetur = $faker->randomElement($detailsData);
        $produkId = $detailToRetur['produk_id'];
        $maxQty = $detailToRetur['qty'];
        $hargaSatuan = $detailToRetur['harga_satuan'];

        $jumlahRetur = $faker->numberBetween(1, $maxQty);
        $nilaiRetur = $jumlahRetur * $hargaSatuan;
        $kodeRetur = 'RPJ' . $faker->unique()->randomNumber(6);

        DB::table('retur_penjualans')->insert([
            'id' => Str::uuid()->toString(),
            'kode_retur' => $kodeRetur,
            'tanggal_retur' => $tanggalRetur,
            'penjualan_id' => $penjualanId,
            'produk_id' => $produkId,
            'jumlah_retur' => $jumlahRetur,
            'alasan_retur' => $faker->randomElement(['Produk rusak', 'Barang cacat', 'Salah kirim barang']),
            'nilai_retur' => $nilaiRetur,
            'user_id' => $userId,
            'created_at' => $tanggalRetur,
            'updated_at' => $tanggalRetur,
        ]);
    }
}
