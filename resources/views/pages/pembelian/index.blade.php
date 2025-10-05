@extends('layouts.layout')
@section('title', 'Pesanan Pembelian')
@section('subtitle', 'Daftar semua transaksi pembelian')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">

                <div class="flex flex-col gap-2">
                    <form action="{{ route('pesanan-pembelian.index') }}" method="GET" class="flex items-center gap-2">
                        <div class="relative w-64 sm:w-72">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari kode/pemasok..."
                                class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                <i class="bx bx-search text-lg"></i>
                            </span>
                        </div>
                        <div class="relative group">
                            <a href="{{ route('pesanan-pembelian.index') }}"
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

                <div class="flex items-center gap-2">
                    <a href="{{ route('pembelian.create') }}"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <i class="bx bx-plus-circle text-lg mr-1"></i> Tambah Pembelian
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
                                Kode Pembelian
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Tanggal
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Pemasok
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                Total Bayar
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-24">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pembelians as $pembelian)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $loop->iteration + $pembelians->firstItem() - 1 }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-mono">
                                    {{ $pembelian->kode_pembelian }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $pembelian->pemasok->nama_pemasok ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-semibold">
                                    Rp {{ number_format($pembelian->total_bayar, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 flex items-center gap-2">
                                    <a href="{{ route('pesanan-pembelian.show', $pembelian->id) }}"
                                        class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200 hover:bg-gray-50">
                                        <i class="bx bx-show text-base"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                    data pesanan pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $pembelians->links('vendor.pagination.tailwind') }}
        </div>
    </div>
@endsection
