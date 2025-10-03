<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'stok_produk',
        'harga_beli',
        'harga_jual',
        'photo_produk',
        'deskripsi_produk',
        'is_active',
        'satuan_id',
        'kategori_id',
        'merek_id',
    ];

    protected $attributes = [
        'is_active' => 'active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produk) {
            // generate UUID
            if (empty($produk->id)) {
                $produk->id = (string) Str::uuid();
            }

            // generate kode_produk otomatis
            if (empty($produk->kode_produk)) {
                $latestProduk = static::where('kode_produk', 'like', 'PRD%')
                    ->orderBy('kode_produk', 'desc')
                    ->first();

                if ($latestProduk) {
                    $number = intval(substr($latestProduk->kode_produk, 3)) + 1;
                } else {
                    $number = 1;
                }

                $produk->kode_produk = 'PRD' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // relasi
    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function merek(): BelongsTo
    {
        return $this->belongsTo(Merek::class);
    }
}
