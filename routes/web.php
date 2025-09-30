<?php

use Illuminate\Support\Facades\Route;

Route::get('/tables', function () {
    return view('pages.kategori.index');
});
