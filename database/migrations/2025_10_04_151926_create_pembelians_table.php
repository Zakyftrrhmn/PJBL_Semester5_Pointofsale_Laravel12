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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_pembelian')->unique();
            $table->date('tanggal_pembelian');
            $table->unsignedBigInteger('total_harga');
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('ppn')->default(0);
            $table->unsignedBigInteger('total_bayar');
            $table->foreignUuid('pemasok_id')->constrained('pemasoks')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
