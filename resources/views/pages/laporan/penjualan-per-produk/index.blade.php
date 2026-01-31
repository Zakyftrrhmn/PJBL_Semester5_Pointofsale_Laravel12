@extends('layouts.layout')
@section('title', 'Laporan Penjualan Per Produk')
@section('subtitle', 'Laporan detail penjualan berdasarkan produk dengan filter kode unit')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- WIDGET RINGKASAN --}}
        {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty Terjual</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($total_qty, 0, ',', '.') }} <span class="text-sm font-normal text-gray-500">pcs</span>
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-indigo-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pendapatan</p>
                <p class="mt-1 text-xl font-bold text-gray-900">Rp{{ number_format($total_subtotal, 0, ',', '.') }}</p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-orange-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</p>
                <p class="mt-1 text-xl font-bold text-gray-900">Rp{{ number_format($total_modal, 0, ',', '.') }}</p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Laba</p>
                <p class="mt-1 text-xl font-bold text-green-600">Rp{{ number_format($total_laba, 0, ',', '.') }}</p>
            </div>
        </div> --}}

        {{-- FORM FILTER --}}
        <form action="{{ route('laporan.penjualan-per-produk.index') }}" method="GET"
            class="p-5 bg-white rounded-xl shadow-sm border border-gray-200 space-y-4" x-data="{ preset: '{{ request('preset', 'all') }}', startDate: '{{ request('start_date') }}', endDate: '{{ request('end_date') }}' }">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">

                {{-- Periode --}}
                <div>
                    <label for="preset" class="block text-sm font-medium text-gray-700">Periode</label>
                    <select id="preset" name="preset" x-model="preset"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                        <option value="all">Seluruh Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="this_week">Minggu Ini</option>
                        <option value="this_month">Bulan Ini</option>
                        <option value="this_year">Tahun Ini</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                {{-- Kode Unit (PREFIX) --}}
                <div>
                    <label for="prefix" class="block text-sm font-medium text-gray-700">Kode Unit</label>
                    <select id="prefix" name="prefix"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                        <option value="all" {{ request('prefix') == 'all' ? 'selected' : '' }}>Semua Kode</option>
                        @foreach ($prefixes as $p)
                            <option value="{{ $p }}" {{ request('prefix') == $p ? 'selected' : '' }}>
                                {{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div>
                    <label for="kategori_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select id="kategori_id" name="kategori_id"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                        <option value="all" {{ request('kategori_id') == 'all' ? 'selected' : '' }}>Semua Kategori
                        </option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="return" {{ request('status') == 'return' ? 'selected' : '' }}>Retur</option>
                    </select>
                </div>

                {{-- Tombol Aksi --}}
                <div class="sm:col-span-2 flex items-end gap-2">
                    <button type="submit"
                        class="flex items-center justify-center gap-2 px-4 py-2 w-full rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                        <i class='bx bx-filter-alt'></i>
                        <span>Filter</span>
                    </button>

                    @php
                        $exportParams = request()->query();
                    @endphp

                    <a href="{{ route('laporan.penjualan-per-produk.export.pdf', $exportParams) }}" target="_blank"
                        title="Export PDF"
                        class="p-2 rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        <i class='bx bxs-file-pdf text-xl'></i>
                    </a>
                    <a href="{{ route('laporan.penjualan-per-produk.export.excel', $exportParams) }}" target="_blank"
                        title="Export Excel"
                        class="p-2 rounded-md border border-green-200 bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                        <i class='bx bxs-spreadsheet text-xl'></i>
                    </a>
                </div>
            </div>

            {{-- Custom Date Range --}}
            <div class="flex space-x-2" x-show="preset == 'custom'" x-cloak>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">Mulai</label>
                    <input type="date" name="start_date" x-model="startDate" :required="preset == 'custom'"
                        class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">Sampai</label>
                    <input type="date" name="end_date" x-model="endDate" :required="preset == 'custom'"
                        class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
        </form>

        {{-- TABLE DATA --}}
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-blue-600">
                                Subtotal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-orange-600">
                                Modal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-green-600">
                                Laba</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($detailPenjualans as $index => $detail)
                            @php
                                $modal = $detail->qty * ($detail->produk->harga_beli ?? 0);
                                $laba = $detail->subtotal - $modal;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    {{ $detailPenjualans->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $detail->penjualan->kode_penjualan }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($detail->penjualan->tanggal_penjualan)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">
                                    {{ $detail->produk->kode_produk ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $detail->produk->nama_produk ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    {{ $detail->produk->kategori->nama_kategori ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-900">
                                    {{ $detail->qty }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-right text-gray-900">
                                    {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-orange-600">
                                    {{ number_format($modal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-right text-green-600">
                                    {{ number_format($laba, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-10 text-center text-gray-400 italic bg-gray-50">
                                    Tidak ada data penjualan produk yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $detailPenjualans->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
