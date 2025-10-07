<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ReturPenjualan;

class Penjualan extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_penjualan',
        'tanggal_penjualan',
        'total_harga',
        'diskon',
        'total_bayar',
        'jumlah_bayar',
        'kembalian',
        'pelanggan_id',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penjualan) {
            if (empty($penjualan->id)) {
                $penjualan->id = (string) Str::uuid();
            }

            if (empty($penjualan->kode_penjualan)) {
                // Contoh: PJL20240001
                $datePrefix = 'PJL' . date('Y');
                $latestPenjualan = static::where('kode_penjualan', 'like', $datePrefix . '%')
                    ->orderBy('kode_penjualan', 'desc')
                    ->first();

                $number = ($latestPenjualan) ? intval(substr($latestPenjualan->kode_penjualan, -4)) + 1 : 1;
                $penjualan->kode_penjualan = $datePrefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function returPenjualans()
    {
        return $this->hasMany(ReturPenjualan::class, 'penjualan_id');
    }

    public function getStatusAttribute()
    {
        if ($this->returPenjualans()->exists()) {
            return 'Returned';
        }

        return 'Completed';
    }
}
