<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenjualan extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'qty',
        'harga_satuan',
        'diskon_percent',
        'diskon_nominal',
        'subtotal',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detail) {
            if (empty($detail->id)) {
                $detail->id = (string) Str::uuid();
            }
        });
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
