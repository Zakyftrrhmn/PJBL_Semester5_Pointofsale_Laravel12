@extends('layouts.layout')
@section('title', 'Riwayat Penjualan')
@section('subtitle', 'Daftar semua transaksi penjualan yang telah dilakukan')

@section('content')
    {{-- CATATAN: Pastikan Alpine.js diinisialisasi di layout utama Anda, dan variabel 'showModal' serta 'deleteUrl' tersedia dalam scope ini untuk tombol Hapus berfungsi. --}}
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kasir</th>
                                {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th> --}}
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th> {{-- **REVISI: Ditambah/Diubah menjadi text-center** --}}
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ $penjualan->user->name ?? 'N/A' }}
                                    </td>
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
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
                                    </td> --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center gap-2"> {{-- **REVISI: Menggunakan justify-center** --}}

                                            @can('invoice.show')
                                                <a href="{{ route('invoice.show', $penjualan->id) }}"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200"
                                                    title="Lihat Detail">
                                                    <i class='bx bx-show text-base'></i>
                                                </a>
                                            @endcan

                                            @if ($penjualan->status !== 'Returned')
                                                @can('invoice.edit')
                                                    <a href="{{ route('invoice.edit', $penjualan->id) }}"
                                                        class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200 hover:bg-gray-50"
                                                        title="Edit">
                                                        <i class="bx bx-edit text-base"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            @if ($penjualan->status !== 'Returned')
                                                @can('invoice.destroy')
                                                    <button
                                                        @click="showModal = true; deleteUrl = '{{ route('invoice.destroy', $penjualan->id) }}'"
                                                        class="inline-flex items-center justify-center rounded-lg p-2 border text-xs shadow-sm text-gray-700 border-gray-200"
                                                        title="Hapus">
                                                        <i class="bx bx-trash text-base"></i>
                                                    </button>
                                                @endcan
                                            @endif
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
