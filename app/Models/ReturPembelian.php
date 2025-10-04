<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturPembelian extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_retur',
        'tanggal_retur',
        'pembelian_id',
        'produk_id',
        'jumlah_retur',
        'alasan_retur',
        'nilai_retur',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($retur) {
            if (empty($retur->id)) {
                $retur->id = (string) Str::uuid();
            }

            // generate kode_retur otomatis: RB25100001 (RB + YYMM + 0001)
            if (empty($retur->kode_retur)) {
                $latestRetur = static::where('kode_retur', 'like', 'RB%')
                    ->orderBy('kode_retur', 'desc')
                    ->first();

                $prefix = 'RB' . date('ym');
                $number = 1;

                if ($latestRetur) {
                    $lastNumber = intval(substr($latestRetur->kode_retur, 6));
                    if (substr($latestRetur->kode_retur, 0, 6) == $prefix) {
                        $number = $lastNumber + 1;
                    }
                }

                $retur->kode_retur = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relasi
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
