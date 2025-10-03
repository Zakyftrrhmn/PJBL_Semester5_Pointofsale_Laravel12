<?php

use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MerekController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PemasokController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // Kategori
    Route::resource('kategori', KategoriController::class);
    // Merek
    Route::resource('merek', MerekController::class);

    // pelanggan
    Route::resource('pelanggan', PelangganController::class);
    Route::get('pelanggan-export-excel', [PelangganController::class, 'exportExcel'])->name('pelanggan.export.excel');
    Route::get('pelanggan-export-pdf', [PelangganController::class, 'exportPDF'])->name('pelanggan.export.pdf');

    // pemasok
    Route::resource('pemasok', PemasokController::class);
    Route::get('pemasok-export-excel', [PemasokController::class, 'exportExcel'])->name('pemasok.export.excel');
    Route::get('pemasok-export-pdf', [PemasokController::class, 'exportPDF'])->name('pemasok.export.pdf');
});
