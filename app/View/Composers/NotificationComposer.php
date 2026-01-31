<?php

namespace App\View\Composers;

use App\Models\Produk;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $produks = Produk::where('is_active', 'active')
            ->select('id', 'nama_produk', 'stok_produk', 'pengingat_stok', 'photo_produk', 'kode_produk', 'updated_at')
            ->get();

        $dataAI = $produks->map(function ($p) {

            // ==========================
            // 1. Hitung penjualan 7 hari terakhir
            // ==========================
            $qty7 = $p->detailPenjualan()
                ->where('created_at', '>=', now()->subDays(7))
                ->sum('qty');

            $avg = max($qty7 / 7, 0.1); // hindari pembagian nol

            // Prediksi stok habis
            $habisHari = $p->stok_produk / $avg;


            // ==========================
            // 2. Deteksi PRODUK TIDAK LAKU ("slow moving")
            // ==========================

            $lastSale = $p->detailPenjualan()->latest()->first();
            $daysNoSale = $lastSale
                ? now()->diffInDays($lastSale->created_at)
                : 90; // belum pernah terjual

            $isSlowMoving = $daysNoSale >= 30;


            // ==========================
            // 3. Tentukan STATUS (urgent, warning, slow, normal)
            // ==========================

            if ($isSlowMoving) {
                $status = "slow";
            } elseif ($habisHari <= 3 || $p->stok_produk <= ($p->pengingat_stok * 0.5)) {
                $status = "urgent";
            } elseif ($p->stok_produk <= $p->pengingat_stok) {
                $status = "warning";
            } else {
                $status = "normal";
            }


            // ==========================
            // 4. Hitung rekomendasi restock (hanya urgent/warning)
            // ==========================

            $hariTarget = $status === "urgent" ? 14 : 7;
            $stokIdeal = ceil($avg * $hariTarget);
            $saranBeli = max($stokIdeal - $p->stok_produk, 0);


            return (object)[
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'stok_produk' => $p->stok_produk,
                'pengingat_stok' => $p->pengingat_stok,
                'photo_produk' => $p->photo_produk,
                'kode_produk' => $p->kode_produk,
                'avg_penjualan' => round($avg, 1),
                'habis_hari' => round($habisHari),
                'status' => $status,
                'rekomendasi_beli' => $saranBeli,
                'days_no_sale' => $daysNoSale,
                'updated_at' => $p->updated_at,
            ];
        })
            ->filter(fn($p) => $p->status !== "normal"); // tampilkan urgent, warning, slow

        $view->with('stokRendahProduks', $dataAI);
    }
}
