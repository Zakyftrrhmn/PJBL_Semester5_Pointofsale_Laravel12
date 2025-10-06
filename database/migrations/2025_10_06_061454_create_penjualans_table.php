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
            $table->string('kode_penjualan', 20)->unique(); // Contoh: PJL20240001
            $table->date('tanggal_penjualan');
            $table->decimal('total_harga', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2); // Setelah diskon
            $table->decimal('jumlah_bayar', 15, 2); // Uang yang dibayarkan pelanggan
            $table->decimal('kembalian', 15, 2)->default(0);

            // Relasi Pelanggan (Sudah BENAR menggunakan UUID)
            // Asumsi: 'pelanggans.id' bertipe UUID
            $table->foreignUuid('pelanggan_id')->constrained('pelanggans')->onDelete('cascade');

            // Relasi User/Kasir (Perbaikan: Menggunakan foreignId() yang otomatis membuat kolom dan constraint)
            // Asumsi: 'users.id' bertipe integer (standar Laravel)
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
