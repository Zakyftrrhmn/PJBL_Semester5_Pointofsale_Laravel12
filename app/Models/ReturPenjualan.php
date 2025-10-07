<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;


class ReturPenjualan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'kode_retur',
        'tanggal_retur',
        'penjualan_id',
        'produk_id',
        'jumlah_retur',
        'alasan_retur',
        'nilai_retur',
        'user_id',
    ];

    protected $casts = [
        'tanggal_retur' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
    // Relasi ke Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    // Relasi ke Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    // Relasi ke User/Kasir
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Menggunakan UUID sebagai key
    protected $keyType = 'string';
    public $incrementing = false;
}
