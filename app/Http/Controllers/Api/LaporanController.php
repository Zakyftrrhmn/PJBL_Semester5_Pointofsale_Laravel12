<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan; // Pastikan model Penjualan sudah di-import
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Helper untuk menentukan rentang tanggal berdasarkan preset atau tanggal kustom
     */
    protected function getDateRange(string $preset, ?string $startDate, ?string $endDate): array
    {
        $now = now();
        $start = null;
        $end = null;

        switch ($preset) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek(Carbon::MONDAY);
                $end   = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $start = Carbon::parse($startDate)->startOfDay();
                    $end   = Carbon::parse($endDate)->endOfDay();
                }
                break;
            default: // Default ke bulan ini jika tidak ada filter
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    /**
     * [READ] Menampilkan laporan penjualan berdasarkan filter tanggal.
     * Endpoint: GET /api/laporan/penjualan
     * Query Params: preset, start_date, end_date
     */
    public function indexPenjualan(Request $request)
    {
        // 1. Tentukan Rentang Tanggal
        $preset = $request->get('preset', 'this_month'); // Default: bulan ini
        $startDateParam = $request->get('start_date');
        $endDateParam = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($preset, $startDateParam, $endDateParam);

        // Cek jika tanggal tidak valid
        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Filter tanggal tidak valid.'], 400);
        }

        // 2. Query Data Penjualan
        $penjualans = Penjualan::with(['pelanggan:id,nama_pelanggan'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(20) // Gunakan pagination untuk mobile
            ->withQueryString();

        // 3. Hitung Ringkasan Total (untuk semua data, bukan hanya yang dipaginasi)
        $totalQuery = Penjualan::whereBetween('created_at', [$startDate, $endDate]);

        $totalPenjualanKotor = (float) $totalQuery->sum('total_bayar');
        $totalDiskonTransaksi = (float) $totalQuery->sum('diskon_transaksi');
        $totalPenjualanBersih = $totalPenjualanKotor - $totalDiskonTransaksi;

        $totalLaba = 0; // Perlu join ke detail dan produk untuk menghitung laba, ini bisa jadi kompleks untuk laporan sederhana.
        // Jika model Penjualan menyimpan laba, gunakan itu. Jika tidak, hitung manual:
        // $totalLaba = $totalQuery->sum('laba_bersih_transaksi'); // Asumsi field ini ada

        // 4. Format Output
        return response()->json([
            'filter_info' => [
                'preset' => $preset,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_transaksi' => $penjualans->total(),
                'penjualan_kotor' => $totalPenjualanKotor,
                'diskon_transaksi' => $totalDiskonTransaksi,
                'penjualan_bersih' => $totalPenjualanBersih,
                'total_laba' => 0.0, // Isi dengan perhitungan laba yang sesuai jika sudah diimplementasikan
            ],
            'data' => $penjualans,
        ], 200);
    }
}
