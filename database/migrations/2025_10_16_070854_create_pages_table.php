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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko')->nullable();
            $table->string('nama_pemilik')->nullable();
            $table->string('logo_sidebar')->nullable();
            $table->string('logo_sidebar2')->nullable();
            $table->string('logo_login')->nullable();
            $table->string('favicon')->nullable();

            // Pecahan alamat
            $table->string('jalan')->nullable();        // Contoh: "Jl. Jend. Ahmad Yani No.157"
            $table->string('kelurahan')->nullable();   // Contoh: "Tanah Datar"
            $table->string('kecamatan')->nullable();   // Contoh: "Pekanbaru Kota"
            $table->string('kota')->nullable();        // Contoh: "Kota Pekanbaru"
            $table->string('provinsi')->nullable();    // Contoh: "Riau"
            $table->string('kode_pos', 10)->nullable(); // Contoh: "28156"

            $table->string('telepon')->nullable();
            $table->string('telepon2')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
