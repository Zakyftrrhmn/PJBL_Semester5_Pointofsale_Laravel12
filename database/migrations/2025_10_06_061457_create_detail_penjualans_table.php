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
            $table->id();
            $table->uuid('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualans')->onDelete('cascade');

            $table->uuid('produk_id');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('restrict'); // Pastikan produk tidak terhapus jika masih ada di penjualan

            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

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
