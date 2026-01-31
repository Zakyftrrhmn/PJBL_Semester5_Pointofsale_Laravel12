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
    // Tambahkan attribute ke array appends agar muncul saat di-convert ke array/json (opsional)
    protected $appends = ['total_modal', 'laba'];

    protected $fillable = [
        'kode_penjualan',
        'tanggal_penjualan',
        'total_harga',
        'diskon_percent', // <-- GANTI DENGAN INI
        'diskon_nominal', // <-- DAN INI
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
            // 1. Generate UUID
            if (empty($penjualan->id)) {
                $penjualan->id = (string) Str::uuid();
            }

            // 2. Generate kode_penjualan otomatis (Contoh: IPM26010001)
            if (empty($penjualan->kode_penjualan)) {
                // Gunakan date('ym') untuk Tahun (2 digit) dan Bulan (2 digit)
                // Hasil prefix: IPM2601 (untuk Jan 2026)
                $datePrefix = 'IPM' . date('ym');

                $latestPenjualan = static::where('kode_penjualan', 'like', $datePrefix . '%')
                    ->orderBy('kode_penjualan', 'desc')
                    ->first();

                $number = 1;

                if ($latestPenjualan) {
                    // Mengambil 4 angka terakhir dari string kode_penjualan
                    $lastNumber = intval(substr($latestPenjualan->kode_penjualan, -4));
                    $number = $lastNumber + 1;
                }

                // Gabungkan prefix dengan nomor urut yang sudah di-pad dengan nol (0001)
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

    /**
     * Menghitung total modal berdasarkan harga beli produk di setiap detail
     */
    public function getTotalModalAttribute()
    {
        // Gunakan relasi yang sudah di-load agar tidak query berulang (N+1)
        // dan pastikan menggunakan nama fungsi relasi yang benar
        return $this->detailPenjualans->sum(function ($detail) {
            // Cek apakah produk ada, jika null (produk dihapus) beri 0
            $hargaBeli = $detail->produk ? (float) $detail->produk->harga_beli : 0;
            return (int) $detail->qty * $hargaBeli;
        });
    }

    /**
     * Menghitung laba (Total Bayar - Total Modal)
     */
    public function getLabaAttribute()
    {
        // Pastikan total_bayar dikonversi ke float/int agar perhitungan akurat
        return (float) $this->total_bayar - (float) $this->total_modal;
    }
}
