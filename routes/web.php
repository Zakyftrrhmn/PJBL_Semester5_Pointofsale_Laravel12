<?php

use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::resource('kategori', KategoriController::class);
    Route::resource('pelanggan', PelangganController::class);

    // Export routes
    Route::get('pelanggan-export-excel', [PelangganController::class, 'exportExcel'])->name('pelanggan.export.excel');
    Route::get('pelanggan-export-pdf', [PelangganController::class, 'exportPDF'])->name('pelanggan.export.pdf');
});
