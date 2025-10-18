<?php

namespace Database\Seeders;

use App\Models\Pembelian;
use App\Models\Pemasok;
use App\Models\Produk;
use App\Models\DetailPembelian;
use App\Models\ReturPembelian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pemasoks = Pemasok::pluck('id')->toArray();
        $produks  = Produk::pluck('id')->toArray();

        if (empty($pemasoks) || empty($produks)) {
            $this->command->info('Tidak dapat membuat Pembelian Seeder. Pastikan tabel Pemasok dan Produk memiliki data.');
            return;
        }

        $totalPembelian = 500;
        $maxRetur = 5; // <<< hanya 5 retur saja
        $returCount = 0;

        $this->command->info("Membuat {$totalPembelian} data Pembelian, Detail Pembelian, dan maksimal {$maxRetur} Retur...");

        DB::beginTransaction();

        try {
            $faker = \Faker\Factory::create('id_ID');

            for ($i = 0; $i < $totalPembelian; $i++) {
                $tanggalPembelian = $faker->dateTimeBetween('-1 year', 'now');
                $itemsCount = $faker->numberBetween(1, 5);
                $selectedProdukIds = $faker->randomElements($produks, $itemsCount);

                $totalHarga = 0;
                $detailsData = [];

                foreach ($selectedProdukIds as $produkId) {
                    $produk = Produk::find($produkId);
                    if (!$produk) continue;

                    $hargaBeli = $produk->harga_beli;
                    $jumlah = $faker->numberBetween(5, 50);
                    $subtotal = $hargaBeli * $jumlah;
                    $totalHarga += $subtotal;

                    $detailsData[] = [
                        'produk_id'  => $produkId,
                        'jumlah'     => $jumlah,
                        'harga_beli' => $hargaBeli,
                        'subtotal'   => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Tambah stok produk
                    DB::table('produks')->where('id', $produkId)
                        ->increment('stok_produk', $jumlah);
                }

                $diskon = $faker->numberBetween(0, (int)($totalHarga * 0.05));
                $ppn = (int)(($totalHarga - $diskon) * 0.11);
                $totalBayar = $totalHarga - $diskon + $ppn;

                $pembelian = Pembelian::create([
                    'kode_pembelian'    => 'PO-' . strtoupper(uniqid()),
                    'tanggal_pembelian' => $tanggalPembelian,
                    'pemasok_id'        => $faker->randomElement($pemasoks),
                    'total_harga'       => $totalHarga,
                    'diskon'            => $diskon,
                    'ppn'               => $ppn,
                    'total_bayar'       => $totalBayar,
                    'created_at'        => $tanggalPembelian,
                    'updated_at'        => $tanggalPembelian,
                ]);

                foreach ($detailsData as $detail) {
                    $detail['pembelian_id'] = $pembelian->id;
                    DetailPembelian::create($detail);
                }

                // === Hanya buat 5 retur pertama ===
                if ($returCount < $maxRetur) {
                    $detailToRetur = $faker->randomElement($detailsData);
                    $jumlahBeli = $detailToRetur['jumlah'];
                    $jumlahRetur = $faker->numberBetween(1, ceil($jumlahBeli / 2));
                    $nilaiRetur = $jumlahRetur * $detailToRetur['harga_beli'];
                    $tanggalRetur = $faker->dateTimeBetween($tanggalPembelian, 'now');

                    ReturPembelian::create([
                        'kode_retur'    => 'RT-' . $faker->unique()->randomNumber(6),
                        'tanggal_retur' => $tanggalRetur,
                        'pembelian_id'  => $pembelian->id,
                        'produk_id'     => $detailToRetur['produk_id'],
                        'jumlah_retur'  => $jumlahRetur,
                        'alasan_retur'  => $faker->randomElement(['Barang rusak', 'Salah kirim barang', 'Kelebihan jumlah']),
                        'nilai_retur'   => $nilaiRetur,
                        'created_at'    => $tanggalRetur,
                        'updated_at'    => $tanggalRetur,
                    ]);

                    DB::table('produks')->where('id', $detailToRetur['produk_id'])
                        ->decrement('stok_produk', $jumlahRetur);

                    $returCount++;
                }

                if (($i + 1) % 100 === 0) {
                    $this->command->info("Progress: " . ($i + 1) . " data berhasil dibuat.");
                }
            }

            DB::commit();
            $this->command->info("Selesai! {$totalPembelian} Pembelian dibuat, dengan {$returCount} Retur saja.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal melakukan seeding: ' . $e->getMessage());
        }
    }
}
