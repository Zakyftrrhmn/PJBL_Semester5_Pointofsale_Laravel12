<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pages extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_toko',
        'nama_pemilik',
        'logo_sidebar',
        'logo_sidebar2',
        'logo_login',
        'favicon',

        'jalan',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',

        'telepon',
        'telepon2',
        'email',
    ];

    /**
     * AUTO CLEAR CACHE SAAT DATA PAGES BERUBAH
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('app_pages');
        });

        static::deleted(function () {
            Cache::forget('app_pages');
        });
    }
}
