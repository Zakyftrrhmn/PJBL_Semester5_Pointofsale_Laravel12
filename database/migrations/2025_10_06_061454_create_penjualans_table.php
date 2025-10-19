<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_penjualan', 20)->unique(); // Akan digenerate di model jika diperlukan
            $table->date('tanggal_penjualan');

            // total bruto = jumlah semua subtotal (setelah diskon produk)
            $table->decimal('total_harga', 15, 2);

            // Diskon transaksi: persen dan nominal
            $table->decimal('diskon_percent', 8, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);

            // total setelah diskon transaksi
            $table->decimal('total_bayar', 15, 2);

            $table->decimal('jumlah_bayar', 15, 2); // Uang yang dibayarkan pelanggan
            $table->decimal('kembalian', 15, 2)->default(0);

            // Relasi Pelanggan (UUID)
            $table->foreignUuid('pelanggan_id')->constrained('pelanggans')->onDelete('cascade');

            // Relasi User/Kasir (asumsi users.id = integer)
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
