<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProdukExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Produk::with(['kategori', 'merek', 'satuan'])
            ->get()
            ->map(function ($produk) {
                return [
                    'ID'            => $produk->id,
                    'Kode Produk'   => $produk->kode_produk,
                    'Nama Produk'   => $produk->nama_produk,
                    'Stok'          => $produk->stok_produk,
                    'Harga Beli'    => $produk->harga_beli,
                    'Harga Jual'    => $produk->harga_jual,
                    'Deskripsi'     => $produk->deskripsi_produk ?? '-',
                    'Status'        => $produk->is_active,
                    'Satuan'        => $produk->satuan->nama_satuan ?? '-',
                    'Kategori'      => $produk->kategori->nama_kategori ?? '-',
                    'Merek'         => $produk->merek->nama_merek ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Produk',
            'Nama Produk',
            'Stok',
            'Harga Beli',
            'Harga Jual',
            'Deskripsi',
            'Status',
            'Satuan',
            'Kategori',
            'Merek',
        ];
    }
}
