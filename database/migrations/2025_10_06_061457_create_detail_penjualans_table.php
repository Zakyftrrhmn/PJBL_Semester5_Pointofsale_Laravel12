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
        Schema::create('detail_penjualans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
            $table->foreignUuid('produk_id')->constrained('produks')->cascadeOnDelete();

            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('harga_satuan');

            // Diskon per produk: persen dan nominal (hasil per-item)
            // persen: 0..100 dengan dua angka desimal, nominal: dalam rupiah
            $table->decimal('diskon_percent', 8, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);

            // subtotal setelah diskon (qty * harga_satuan - diskon_nominal)
            $table->decimal('subtotal', 15, 2);

            $table->timestamps();

            // Unique constraint agar 1 produk hanya muncul 1x di 1 penjualan (opsional)
            $table->unique(['penjualan_id', 'produk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualans');
    }
};
