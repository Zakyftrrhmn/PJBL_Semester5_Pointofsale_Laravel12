@extends('layouts.layout')
@section('title', 'Retur Pembelian')
@section('subtitle', 'Daftar semua retur pembelian')
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
                    <form action="{{ route('retur-pembelian.index') }}" method="GET" class="flex items-center gap-2">
                        <div class="relative w-64 sm:w-72">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari kode/pemasok..."
                                class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                <i class="bx bx-search text-lg"></i>
                            </span>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('retur-pembelian.create') }}"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <i class="bx bx-plus-circle text-lg mr-1"></i> Buat Retur Baru
                    </a>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-10">
                                No
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Kode Retur
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Tanggal
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Transaksi
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Produk
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Jumlah
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Nilai Retur
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse ($returs as $retur)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $loop->iteration + $returs->firstItem() - 1 }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-mono">
                                    {{ $retur->kode_retur }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ \Carbon\Carbon::parse($retur->tanggal_retur)->format('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    <p class="font-mono">{{ $retur->pembelian->kode_pembelian ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">Pemasok:
                                        {{ $retur->pembelian->pemasok->nama_pemasok ?? '-' }}</p>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                    data retur pembelian.</td>
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
