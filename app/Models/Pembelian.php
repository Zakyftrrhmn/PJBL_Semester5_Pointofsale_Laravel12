<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\ReturPembelian;

class Pembelian extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pembelian',
        'tanggal_pembelian',
        'total_harga',
        'diskon',
        'ppn',
        'total_bayar',
        'pemasok_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pembelian) {
            // generate UUID
            if (empty($pembelian->id)) {
                $pembelian->id = (string) Str::uuid();
            }

            // generate kode_pembelian otomatis: BL25100001 (BL + YYMM + 0001)
            if (empty($pembelian->kode_pembelian)) {
                $latestPembelian = static::where('kode_pembelian', 'like', 'BL%')
                    ->orderBy('kode_pembelian', 'desc')
                    ->first();

                $prefix = 'BL' . date('ym');
                $number = 1;

                if ($latestPembelian) {
                    $lastNumber = intval(substr($latestPembelian->kode_pembelian, 6));
                    if (substr($latestPembelian->kode_pembelian, 0, 6) == $prefix) {
                        $number = $lastNumber + 1;
                    }
                }

                $pembelian->kode_pembelian = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relasi
    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class);
    }

    public function detailPembelians()
    {
        return $this->hasMany(DetailPembelian::class);
    }

    public function returPembelians()
    {
        return $this->hasMany(ReturPembelian::class);
    }

    public function getStatusAttribute()
    {
        if ($this->returPembelians()->exists()) {
            return 'Returned';
        }

        return 'Completed';
    }
}
