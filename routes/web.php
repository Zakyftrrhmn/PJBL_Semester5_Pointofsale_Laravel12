<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MerekController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PesananPembelianController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Kategori
    Route::resource('kategori', KategoriController::class);

    // Merek
    Route::resource('merek', MerekController::class);

    // Satuan
    Route::resource('satuan', SatuanController::class);


    // produk
    Route::resource('produk', ProdukController::class);
    Route::get('produk-export-excel', [ProdukController::class, 'exportExcel'])->name('produk.export.excel');
    Route::get('produk-export-pdf', [ProdukController::class, 'exportPDF'])->name('produk.export.pdf');

    Route::get('/barcode', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::post('/barcode/cetak-pdf', [BarcodeController::class, 'cetakPdf'])->name('barcode.cetak-pdf');
    Route::post('/barcode/generate', [BarcodeController::class, 'generateBarcodes'])->name('barcode.generate');

    // pelanggan
    Route::resource('pelanggan', PelangganController::class);
    Route::get('pelanggan-export-excel', [PelangganController::class, 'exportExcel'])->name('pelanggan.export.excel');
    Route::get('pelanggan-export-pdf', [PelangganController::class, 'exportPDF'])->name('pelanggan.export.pdf');

    // pemasok
    Route::resource('pemasok', PemasokController::class);
    Route::get('pemasok-export-excel', [PemasokController::class, 'exportExcel'])->name('pemasok.export.excel');
    Route::get('pemasok-export-pdf', [PemasokController::class, 'exportPDF'])->name('pemasok.export.pdf');

    // Pembelian (Pembelian Baru / Form Transaksi)
    Route::get('pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('pembelian/store', [PembelianController::class, 'store'])->name('pembelian.store');

    // Pesanan Pembelian (Daftar Transaksi)
    Route::resource('pesanan-pembelian', PesananPembelianController::class)->only(['index', 'show']);
    Route::get('pesanan-pembelian-pdf/{pembelian}', [PesananPembelianController::class, 'exportPDF'])->name('pesanan-pembelian.export.pdf');

    // Retur Pembelian
    Route::resource('retur-pembelian', ReturPembelianController::class)->except(['edit', 'update', 'show', 'destroy']);
    Route::get('retur-pembelian/get-produk/{pembelianId}', [ReturPembelianController::class, 'getProdukByPembelian'])->name('retur-pembelian.get-produk');

    // User Management
    Route::resource('user', UserController::class)->except(['show']);

    // Role & Permission Management
    Route::resource('role', RoleController::class);
    Route::put('role/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('role.update.permissions');
});
