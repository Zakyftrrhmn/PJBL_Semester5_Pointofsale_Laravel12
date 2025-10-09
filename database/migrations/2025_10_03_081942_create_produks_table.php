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
        Schema::create('produks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_produk')->unique();
            $table->string('nama_produk');
            $table->unsignedInteger('stok_produk');
            $table->unsignedInteger('pengingat_stok')->default(10);
            $table->unsignedBigInteger('harga_beli');
            $table->unsignedBigInteger('harga_jual');
            $table->string('photo_produk')->nullable();
            $table->string('deskripsi_produk')->nullable();
            $table->enum('is_active', ['active', 'non_active']);
            $table->foreignUuid('satuan_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('kategori_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('merek_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
