<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'alamat',
        'telepon',
        'telepon2',
        'email',
    ];
}
