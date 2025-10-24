<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_pemasok',
        'telp',
        'email',
        'alamat',
        'photo_pemasok',
    ];

    public function pembelians()
    {
        // Pemasok memiliki banyak Pembelian
        return $this->hasMany(Pembelian::class, 'pemasok_id');
    }
}
