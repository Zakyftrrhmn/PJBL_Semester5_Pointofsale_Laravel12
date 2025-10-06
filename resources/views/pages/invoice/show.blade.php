@extends('layouts.layout')
@section('title', 'Detail Invoice')
@section('subtitle', 'Informasi lengkap transaksi: ' . $penjualan->kode_penjualan)

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Detail Transaksi</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 border-b pb-4">
                    <div>
                        <p class="text-sm text-gray-500">Kode Penjualan</p>
                        <p class="font-medium text-lg">{{ $penjualan->kode_penjualan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal</p>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d F Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pelanggan</p>
                        <p class="font-medium">{{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kasir</p>
                        <p class="font-medium">{{ $penjualan->user->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-700 mb-4">Item Penjualan</h3>
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($penjualan->detailPenjualans as $detail)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800">
                                        {{ $detail->produk->nama_produk ?? 'Produk Dihapus' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800 text-right">
                                        {{ number_format($detail->qty, 0, ',', '.') }}
                                        {{ $detail->produk->satuan->nama_satuan ?? 'Unit' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800 text-right">
                                        Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-2">
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-base text-gray-700">Total Harga Awal:</span>
                            <span class="text-base font-semibold text-gray-900">Rp
                                {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-base text-red-600">Diskon:</span>
                            <span class="text-base font-semibold text-red-600">- Rp
                                {{ number_format($penjualan->diskon, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-lg font-bold text-gray-900">Total Bayar (Net):</span>
                            <span class="text-lg font-bold text-blue-600">Rp
                                {{ number_format($penjualan->total_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-700">Uang Bayar (Customer):</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($penjualan->jumlah_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-lg font-bold text-gray-900">Kembalian:</span>
                            <span class="text-lg font-bold text-green-600">Rp
                                {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tombol Cetak Invoice --}}
                <div class="flex justify-end mt-8 gap-3 border-t pt-6">
                    <a href="{{ route('invoice.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <i class='bx bx-list-ul mr-2 text-lg'></i> Kembali ke Riwayat
                    </a>

                    <a href="{{ route('invoice.print.no-diskon', $penjualan->id) }}" target="_blank"
                        class="inline-flex items-center rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-600">
                        <i class='bx bx-printer mr-2 text-lg'></i> Cetak (Tanpa Diskon)
                    </a>

                    @if ($penjualan->diskon > 0)
                        <a href="{{ route('invoice.print.diskon', $penjualan->id) }}" target="_blank"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            <i class='bx bx-printer mr-2 text-lg'></i> Cetak (Dengan Diskon)
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
