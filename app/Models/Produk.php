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
        'pengingat_stok',
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
            // ... (Logika UUID dan Kode Produk)
            if (empty($produk->id)) {
                $produk->id = (string) Str::uuid();
            }
            if (empty($produk->kode_produk)) {
                $latestProduk = static::where('kode_produk', 'like', 'PRD%')
                    ->orderBy('kode_produk', 'desc')
                    ->first();

                $number = ($latestProduk) ? intval(substr($latestProduk->kode_produk, 3)) + 1 : 1;
                $produk->kode_produk = 'PRD' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });

        // =========================================================================
        // PERBAIKAN: Menggunakan nilai string yang benar ('active', 'non_active')
        // =========================================================================
        static::saving(function ($produk) {
            if ($produk->stok_produk <= 0) {
                // Perbaiki nilai menjadi 'non_active' (sesuai ENUM di migrasi)
                $produk->is_active = 'non_active';
            } else {
                // Pastikan nilai 'active' juga benar
                $produk->is_active = 'active';
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
