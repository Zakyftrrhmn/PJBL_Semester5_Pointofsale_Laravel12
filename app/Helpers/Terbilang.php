<?php

namespace App\Helpers;

class Terbilang
{
    protected static $angka = [
        '',
        'Satu',
        'Dua',
        'Tiga',
        'Empat',
        'Lima',
        'Enam',
        'Tujuh',
        'Delapan',
        'Sembilan',
        'Sepuluh',
        'Sebelas'
    ];

    /**
     * Wrapper agar kompatibel dengan pemanggilan ::make()
     *
     * @param  int|float|string $number
     * @param  string $suffix
     * @return string
     */
    public static function make($number, $suffix = '')
    {
        if (!is_numeric($number)) {
            $number = 0;
        }

        // jika ada pecahan, ambil bagian bulat (sesuaikan jika mau pembulatan)
        $number = (int) floor($number);

        $terbilang = self::convert($number);

        // Hilangkan spasi berlebih, lalu tambahkan suffix bila ada
        return trim($terbilang) . ($suffix ? ' ' . trim($suffix) : '');
    }

    /**
     * Konversi angka ke kata (rekursif)
     *
     * @param int $n
     * @return string
     */
    public static function convert($n)
    {
        $n = (int) $n;
        $hasil = '';

        if ($n < 12) {
            $hasil = self::$angka[$n];
        } elseif ($n < 20) {
            $hasil = self::convert($n - 10) . ' Belas';
        } elseif ($n < 100) {
            $hasil = self::convert(intval($n / 10)) . ' Puluh' . ($n % 10 ? ' ' . self::convert($n % 10) : '');
        } elseif ($n < 200) {
            $hasil = 'Seratus' . ($n - 100 ? ' ' . self::convert($n - 100) : '');
        } elseif ($n < 1000) {
            $hasil = self::convert(intval($n / 100)) . ' Ratus' . ($n % 100 ? ' ' . self::convert($n % 100) : '');
        } elseif ($n < 2000) {
            $hasil = 'Seribu' . ($n - 1000 ? ' ' . self::convert($n - 1000) : '');
        } elseif ($n < 1000000) {
            $hasil = self::convert(intval($n / 1000)) . ' Ribu' . ($n % 1000 ? ' ' . self::convert($n % 1000) : '');
        } elseif ($n < 1000000000) {
            $hasil = self::convert(intval($n / 1000000)) . ' Juta' . ($n % 1000000 ? ' ' . self::convert($n % 1000000) : '');
        } elseif ($n < 1000000000000) {
            $hasil = self::convert(intval($n / 1000000000)) . ' Miliar' . ($n % 1000000000 ? ' ' . self::convert($n % 1000000000) : '');
        } else {
            $hasil = 'Angka terlalu besar';
        }

        return trim($hasil);
    }
}
