@extends('layouts.layout')
@section('title', 'Retur Penjualan')
@section('subtitle', 'Daftar semua retur penjualan')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">

                <div class="flex flex-col gap-2">
                    <form action="{{ route('retur-penjualan.index') }}" method="GET" class="flex items-center gap-2">
                        <div class="relative w-64 sm:w-72">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari kode retur..."
                                class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                <i class="bx bx-search text-lg"></i>
                            </span>
                        </div>
                        <div class="relative group">
                            <a href="{{ route('retur-penjualan.index') }}"
                                class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                                <i class="bx bx-refresh text-xl"></i>
                            </a>
                            <span
                                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300">
                                Reset
                            </span>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    @can('retur-penjualan.create')
                        <a href="{{ route('retur-penjualan.create') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-transparent bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class='bx bx-plus-circle mr-1'></i>
                            Buat Retur Penjualan
                        </a>
                    @endcan
                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Kode Retur
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Tanggal & Pelanggan
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Produk & Alasan
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Jumlah Retur
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Nilai Retur
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">
                                Kasir
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse ($returs as $retur)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $retur->kode_retur }}
                                    <p class="text-xs text-gray-500">Penjualan:
                                        {{ $retur->penjualan->kode_penjualan ?? '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ \Carbon\Carbon::parse($retur->tanggal_retur)->format('d M Y') }}
                                    <p class="text-xs text-gray-500">Pelanggan:
                                        {{ $retur->penjualan->pelanggan->nama_pelanggan ?? '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $retur->produk->nama_produk ?? '-' }}
                                    <p class="text-xs text-gray-500">Alasan: {{ $retur->alasan_retur }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $retur->jumlah_retur }} pcs
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-semibold">
                                    Rp {{ number_format($retur->nilai_retur, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $retur->user->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                    data retur penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $returs->links('vendor.pagination.tailwind') }}
        </div>
    </div>
@endsection
