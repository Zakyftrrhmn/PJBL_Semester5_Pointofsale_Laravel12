@extends('layouts.layout')
@section('title', 'Detail Pesanan Pembelian')
@section('subtitle', 'Informasi lengkap transaksi pembelian')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">

                <div class="flex justify-between items-start mb-6 border-b pb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Faktur Pembelian</h2>
                        <p class="text-sm text-gray-500">
                            Kode: <span class="font-mono text-base font-semibold">{{ $pembelian->kode_pembelian }}</span>
                        </p>
                        <p class="text-sm text-gray-500">
                            Tanggal: {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d F Y') }}
                        </p>
                    </div>
                    <a href="{{ route('pesanan-pembelian.export.pdf', $pembelian->id) }}" target="_blank"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700">
                        <i class="bx bxs-file-pdf text-lg mr-1"></i> Cetak PDF
                    </a>
                </div>

                {{-- Informasi Pemasok --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Informasi Pemasok</h3>
                        <p class="text-sm font-medium text-gray-700">{{ $pembelian->pemasok->nama_pemasok }}</p>
                        <p class="text-sm text-gray-600">Telp: {{ $pembelian->pemasok->telp }}</p>
                        <p class="text-sm text-gray-600">Email: {{ $pembelian->pemasok->email }}</p>
                        <p class="text-sm text-gray-600">Alamat: {{ $pembelian->pemasok->alamat }}</p>
                    </div>
                </div>

                {{-- Detail Produk --}}
                <h3 class="text-lg font-semibold text-gray-800 mb-2 border-t pt-4">Detail Produk Pembelian</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">Produk</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">Harga Beli</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">Jumlah</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">Satuan</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($pembelian->detailPembelians as $detail)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        {{ $detail->produk->nama_produk }}
                                        <p class="text-xs text-gray-500 font-mono">({{ $detail->produk->kode_produk }})</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        {{ $detail->jumlah }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        {{ $detail->produk->satuan->nama_satuan ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan Total --}}
                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-sm space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Total Harga Bruto:</span>
                            <span class="font-medium text-gray-800">Rp
                                {{ number_format($pembelian->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Diskon:</span>
                            <span class="font-medium text-gray-800">- Rp
                                {{ number_format($pembelian->diskon, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">PPN:</span>
                            <span class="font-medium text-gray-800">+ Rp
                                {{ number_format($pembelian->ppn, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-2">
                            <span class="text-lg font-bold text-gray-800">TOTAL BAYAR:</span>
                            <span class="text-xl font-extrabold text-indigo-600">
                                Rp {{ number_format($pembelian->total_bayar, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>


                {{-- Tombol Aksi --}}
                <div class="flex justify-end mt-6 gap-3 border-t pt-6">
                    <a href="{{ route('pesanan-pembelian.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Kembali
                        ke Daftar</a>
                </div>
            </div>
        </div>
    </div>
@endsection
