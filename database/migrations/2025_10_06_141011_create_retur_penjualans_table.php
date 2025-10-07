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
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_retur', 30)->unique(); // Contoh: RPJ20240001
            $table->date('tanggal_retur');

            // Relasi ke Penjualan
            $table->foreignUuid('penjualan_id')->constrained('penjualans')->cascadeOnDelete();

            // Relasi ke Produk
            $table->foreignUuid('produk_id')->constrained('produks')->restrictOnDelete();

            $table->unsignedInteger('jumlah_retur'); // Qty yang diretur
            $table->string('alasan_retur');
            $table->decimal('nilai_retur', 15, 2); // Jumlah uang yang dikembalikan

            // Relasi ke User/Kasir yang memproses retur (Asumsi 'users.id' adalah integer)
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};
