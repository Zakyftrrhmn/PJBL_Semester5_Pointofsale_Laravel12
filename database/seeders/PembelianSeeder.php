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
        // Pastikan ada data di tabel relasi
        $pemasoks = Pemasok::pluck('id')->toArray();
        $produks  = Produk::pluck('id')->toArray();

        if (empty($pemasoks) || empty($produks)) {
            $this->command->info('Tidak dapat membuat Pembelian Seeder. Pastikan tabel Pemasoks dan Produk memiliki data.');
            return;
        }

        $this->command->info('Membuat 1000 data Pembelian, Detail Pembelian, dan Retur (jika ada)...');

        // Jumlah data yang diinginkan
        $count = 1000;

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $count; $i++) {
                $faker = \Faker\Factory::create('id_ID');

                // Tentukan tanggal pembelian (misalnya 1 tahun ke belakang)
                $tanggalPembelian = $faker->dateTimeBetween('-1 year', 'now');

                // 1. Inisialisasi Produk untuk Pembelian ini
                // Ambil 1 sampai 5 produk acak
                $itemsCount = $faker->numberBetween(1, 5);
                $selectedProdukIds = $faker->randomElements($produks, $itemsCount);

                $totalHarga = 0;
                $detailsData = [];
                $produkUpdates = [];

                foreach ($selectedProdukIds as $produkId) {
                    $produk = Produk::find($produkId);
                    $hargaBeli = $produk->harga_beli;
                    $jumlah = $faker->numberBetween(5, 50);
                    $subtotal = $hargaBeli * $jumlah;
                    $totalHarga += $subtotal;

                    $detailsData[] = [
                        'produk_id'    => $produkId,
                        'jumlah'       => $jumlah,
                        'harga_beli'   => $hargaBeli,
                        'subtotal'     => $subtotal,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    // Persiapan untuk update stok setelah transaksi
                    $produkUpdates[$produkId] = $jumlah;
                }

                // Hitung Diskon dan PPN
                $diskon = $faker->numberBetween(0, (int)($totalHarga * 0.05)); // Maksimal 5%
                $ppn = (int)(($totalHarga - $diskon) * 0.11); // PPN 11%
                $totalBayar = $totalHarga - $diskon + $ppn;

                // 2. Buat Pembelian
                $pembelian = Pembelian::create([
                    'kode_pembelian'    => 'PO-' . $faker->unique()->randomNumber(6),
                    'tanggal_pembelian' => $tanggalPembelian,
                    'pemasok_id'        => $faker->randomElement($pemasoks),
                    'total_harga'       => $totalHarga,
                    'diskon'            => $diskon,
                    'ppn'               => $ppn,
                    'total_bayar'       => $totalBayar,
                    'created_at'        => $tanggalPembelian,
                    'updated_at'        => $tanggalPembelian,
                ]);

                // 3. Buat Detail Pembelian dan Update Stok
                foreach ($detailsData as $detail) {
                    $detail['pembelian_id'] = $pembelian->id;
                    DetailPembelian::create($detail);

                    // Update stok produk
                    DB::table('produks')->where('id', $detail['produk_id'])
                        ->increment('stok_produk', $detail['jumlah']);
                }

                // 4. Secara acak buat Retur (misal 10% kemungkinan retur)
                if ($faker->boolean(10)) {
                    // Pilih satu item detail untuk diretur
                    $detailToRetur = $faker->randomElement($detailsData);
                    $jumlahBeli = $detailToRetur['jumlah'];

                    // Retur maksimal 50% dari jumlah beli
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

                    // Kurangi stok produk
                    DB::table('produks')->where('id', $detailToRetur['produk_id'])
                        ->decrement('stok_produk', $jumlahRetur);
                }

                if (($i + 1) % 100 === 0) {
                    $this->command->info("Progress: " . ($i + 1) . " data berhasil dibuat.");
                }
            }

            DB::commit();
            $this->command->info('1000 Data Pembelian berhasil di-seed!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal melakukan seeding: ' . $e->getMessage());
            $this->command->error('Pastikan UUID dan data relasi sudah terisi (Pemasok, Produk, Satuan, Kategori, Merek).');
        }
    }
}
