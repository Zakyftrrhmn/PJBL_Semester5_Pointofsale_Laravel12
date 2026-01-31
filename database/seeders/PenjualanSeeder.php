<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PenjualanSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $pelanggan = Pelanggan::first();

        // Ambil beberapa produk
        $produkA = Produk::first();       // Akan jadi URGENT (prediksi habis 3 hari)
        $produkB = Produk::skip(1)->first(); // Akan jadi WARNING (stok rendah)
        $produkC = Produk::skip(2)->first(); // Akan jadi WARNING (prediksi 7 hari)
        $produkD = Produk::skip(3)->first(); // Normal

        // Set stok manual agar pasti sesuai
        $produkA->update(['stok_produk' => 5, 'pengingat_stok' => 10]);
        $produkB->update(['stok_produk' => 7, 'pengingat_stok' => 10]);
        $produkC->update(['stok_produk' => 15, 'pengingat_stok' => 10]);
        $produkD->update(['stok_produk' => 40, 'pengingat_stok' => 10]);

        // ========= PRODUK A — URGENT 3 HARI =========
        // Penjualan 30 unit dalam 7 hari → rata2 4–5 per hari → habis dalam 1 hari.
        $this->buatPenjualan($produkA, 30, $user, $pelanggan);

        // ========= PRODUK B — WARNING Stok Rendah =========
        // Tidak banyak penjualan, hanya stok < pengingat
        $this->buatPenjualan($produkB, 2, $user, $pelanggan);

        // ========= PRODUK C — WARNING Prediksi 7 Hari =========
        // Penjualan sedang → stok habis sekitar 6–7 hari
        $this->buatPenjualan($produkC, 10, $user, $pelanggan);

        // ========= PRODUK D — NORMAL =========
        // Penjualan kecil → stok aman
        $this->buatPenjualan($produkD, 1, $user, $pelanggan);

        echo "✅ Seeder penjualan selesai — Semua jenis notifikasi dipicu!\n";
    }

    private function buatPenjualan($produk, $qty, $user, $pelanggan)
    {
        $penjualan = Penjualan::create([
            'id' => Str::uuid(),
            // 'kode_penjualan' => 'IPM' . rand(1000, 9999),
            'tanggal_penjualan' => Carbon::now()->subDays(rand(0, 6))->format('Y-m-d'),
            'total_harga' => $qty * $produk->harga_jual,
            'diskon_percent' => 0,
            'diskon_nominal' => 0,
            'total_bayar' => $qty * $produk->harga_jual,
            'jumlah_bayar' => $qty * $produk->harga_jual,
            'kembalian' => 0,
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $user->id,
        ]);

        DetailPenjualan::create([
            'id' => Str::uuid(),
            'penjualan_id' => $penjualan->id,
            'produk_id' => $produk->id,
            'qty' => $qty,
            'harga_satuan' => $produk->harga_jual,
            'diskon_percent' => 0,
            'diskon_nominal' => 0,
            'subtotal' => $qty * $produk->harga_jual,
        ]);
    }
}
