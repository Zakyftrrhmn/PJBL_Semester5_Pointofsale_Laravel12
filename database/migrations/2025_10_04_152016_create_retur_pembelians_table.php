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
        Schema::create('retur_pembelians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_retur')->unique();
            $table->date('tanggal_retur');
            $table->foreignUuid('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
            $table->foreignUuid('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_retur');
            $table->string('alasan_retur');
            $table->unsignedBigInteger('nilai_retur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelians');
    }
};
