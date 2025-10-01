<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PelangganExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Pelanggan::select('nama_pelanggan', 'telp', 'email')->get();
    }

    public function headings(): array
    {
        return ["Nama Pelanggan", "Telepon", "Email"];
    }
}
