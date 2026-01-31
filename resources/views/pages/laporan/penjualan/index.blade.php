@extends('layouts.layout')
@section('title', 'Laporan Penjualan')
@section('subtitle', 'Daftar transaksi penjualan, modal, dan laba')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- WIDGET RINGKASAN DATA --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bayar (Filtered)</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    Rp{{ number_format($total_bayar_all, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-orange-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    Rp{{ number_format($total_modal_all, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Laba</p>
                <p class="mt-1 text-xl font-bold text-green-600">
                    Rp{{ number_format($total_laba_all, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-sm border-l-4 border-indigo-500">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    {{ $penjualans->total() }} <span class="text-sm font-normal text-gray-500">Records</span>
                </p>
            </div>
        </div>

        {{-- FORM FILTER --}}
        <form action="{{ route('laporan.penjualan.index') }}" method="GET"
            class="p-5 bg-white rounded-xl shadow-sm border border-gray-200 space-y-4 lg:space-y-0 lg:flex lg:items-end lg:space-x-4"
            x-data="{ preset: '{{ request('preset', 'all') }}', startDate: '{{ request('start_date') }}', endDate: '{{ request('end_date') }}' }">

            <div class="flex-shrink-0 w-full lg:w-48">
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



            <div class="flex space-x-2 w-full lg:w-auto" x-show="preset == 'custom'" x-cloak>
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

            <div class="flex-shrink-0 w-full lg:w-40">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="return" {{ request('status') == 'return' ? 'selected' : '' }}>Retur</option>
                </select>
            </div>

            <div class="flex items-center space-x-2 w-full lg:w-auto">
                <button type="submit"
                    class="flex items-center justify-center gap-2 px-4 py-2 w-full lg:w-auto rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class='bx bx-filter-alt'></i>
                    <span>Filter</span>
                </button>

                @php
                    $exportParams = array_merge(request()->query(), [
                        'preset' => request('preset', 'all'),
                        'status' => request('status', 'all'),
                    ]);
                @endphp

                <div class="flex gap-2">
                    <a href="{{ route('laporan.penjualan.export.pdf', $exportParams) }}" target="_blank" title="Export PDF"
                        class="p-2 rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        <i class='bx bxs-file-pdf text-xl'></i>
                    </a>
                    <a href="{{ route('laporan.penjualan.export.excel', $exportParams) }}" target="_blank"
                        title="Export Excel"
                        class="p-2 rounded-md border border-green-200 bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                        <i class='bx bxs-spreadsheet text-xl'></i>
                    </a>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-blue-600">
                                Total Bayar</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-orange-600">
                                Modal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-green-600">
                                Laba</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($penjualans as $index => $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    {{ $penjualans->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $p->kode_penjualan }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    {{ $p->pelanggan->nama_pelanggan ?? 'Umum' }}
                                    <div class="text-xs text-blue-500">Kasir: {{ $p->user->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-right text-gray-900">
                                    {{ number_format($p->total_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-orange-600">
                                    {{ number_format($p->total_modal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-right text-green-600">
                                    {{ number_format($p->laba, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                    @if ($p->returPenjualans->isNotEmpty())
                                        <span
                                            class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-red-100 text-red-600">Retur</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-green-100 text-green-600">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-400 italic bg-gray-50">
                                    Tidak ada data penjualan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $penjualans->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
