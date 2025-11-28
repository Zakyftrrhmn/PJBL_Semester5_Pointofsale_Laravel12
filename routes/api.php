<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Nama Route (Naming) menggunakan awalan 'api.' untuk menghindari konflik
| dengan route di web.php.
*/

// Rute Publik (Otentikasi)
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// Rute Terproteksi (Membutuhkan Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Auth Rute
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Dashboard Rute
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // Pelanggan Rute (CRUD)
    // apiResource otomatis memberi nama: api.pelanggans.index, api.pelanggans.store, dst.
    Route::apiResource('pelanggans', PelangganController::class)->except(['create', 'edit'])->names('api.pelanggans');

    // Riwayat Penjualan Rute
    Route::prefix('riwayat-penjualan')->name('api.riwayat-penjualan.')->group(function () {
        Route::get('/', [PenjualanController::class, 'index'])->name('index'); // api.riwayat-penjualan.index
        Route::get('/{penjualan}', [PenjualanController::class, 'show'])->name('show'); // api.riwayat-penjualan.show
        Route::get('/{penjualan}/print', [PenjualanController::class, 'printInvoice'])->name('print'); // api.riwayat-penjualan.print
    });

    // Produk Rute (CRUD)
    // apiResource otomatis memberi nama: api.produks.index, api.produks.store, dst.
    Route::apiResource('produks', ProdukController::class)->except(['create', 'edit'])->names('api.produks');
    Route::get('produks/kategoris', [ProdukController::class, 'getKategoris'])->name('api.produks.kategoris');

    // Laporan Rute
    Route::prefix('laporan')->name('api.laporan.')->group(function () {
        Route::get('penjualan', [LaporanController::class, 'indexPenjualan'])->name('penjualan.index'); // api.laporan.penjualan.index
    });
});
