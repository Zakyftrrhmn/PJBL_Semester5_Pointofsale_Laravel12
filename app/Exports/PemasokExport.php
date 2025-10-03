<?php

namespace App\Exports;

use App\Models\Pemasok;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PemasokExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Pemasok::select('nama_pelanggan', 'telp', 'email', 'email')->get();
    }

    public function headings(): array
    {
        return ["Nama Pelanggan", "Telepon", "Email", "Alamat"];
    }
}
