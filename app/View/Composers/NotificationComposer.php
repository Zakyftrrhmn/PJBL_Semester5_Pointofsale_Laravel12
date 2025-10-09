<?php

namespace App\View\Composers;

use App\Models\Produk;
use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $stokRendahProduks = Produk::whereColumn('stok_produk', '<=', 'pengingat_stok')
            ->where('is_active', 'active')
            ->select('id', 'nama_produk', 'stok_produk', 'pengingat_stok', 'photo_produk', 'kode_produk', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();


        $view->with('stokRendahProduks', $stokRendahProduks);
    }
}
