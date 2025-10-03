<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory, HasUuids;


    public $incrementing = false;   // biar Laravel tahu ini bukan auto increment
    protected $keyType = 'string';  // UUID berupa string


    protected $fillable = ['nama_satuan'];

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}
