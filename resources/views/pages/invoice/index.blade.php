@extends('layouts.layout')
@section('title', 'Riwayat Penjualan')
@section('subtitle', 'Daftar semua transaksi penjualan yang telah dilakukan')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">

                <div class="mb-4 flex justify-between items-center">
                    <form action="{{ route('invoice.index') }}" method="GET" class="w-full max-w-xs">
                        <input type="text" name="search" placeholder="Cari Kode Transaksi / Pelanggan"
                            value="{{ request('search') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-400 focus:ring-blue-100">
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Penjualan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total (Net)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diskon</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kasir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($penjualans as $penjualan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        <a href="{{ route('invoice.show', $penjualan->id) }}" class="hover:underline">
                                            {{ $penjualan->kode_penjualan }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ $penjualan->pelanggan->nama_pelanggan ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-right">
                                        Rp {{ number_format($penjualan->total_bayar, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 text-right">
                                        Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ $penjualan->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        @if ($penjualan->status === 'Returned')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Returned
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('invoice.show', $penjualan->id) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                <i class='bx bx-search text-xl'></i>
                                            </a>
                                            {{-- Tambahkan tombol print langsung ke detail untuk pilihan cetak --}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Tidak ada data transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $penjualans->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
