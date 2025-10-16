@extends('layouts.layout')
@section('title', 'Laporan Pembelian')
@section('subtitle', 'Daftar transaksi pembelian')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- WIDGET RINGKASAN DATA --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 bg-white rounded-xl shadow-lg border-l-4 border-blue-500">
                <p class="text-xs font-medium text-gray-500 uppercase">Periode Tampil</p>
                {{-- Menampilkan tanggal yang sudah dihitung oleh controller --}}
                <p class="mt-1 text-base font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($start_date_filtered)->format('d M Y') }} s/d
                    {{ \Carbon\Carbon::parse($end_date_filtered)->format('d M Y') }}
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-lg border-l-4 border-green-500">
                <p class="text-xs font-medium text-gray-500 uppercase">Total Transaksi</p>
                <p class="mt-1 text-base font-semibold text-gray-900">
                    {{ $pembelians->total() }} Transaksi
                </p>
            </div>
            <div class="p-5 bg-white rounded-xl shadow-lg border-l-4 border-indigo-500">
                <p class="text-xs font-medium text-gray-500 uppercase">Total Bayar (Filtered)</p>
                <p class="mt-1 text-base font-semibold text-gray-900">
                    Rp{{ number_format($total_bayar_all, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- FORM FILTER DENGAN PRESET DAN STATUS (ALPINE JS) --}}
        <form action="{{ route('laporan.pembelian.index') }}" method="GET"
            class="p-5 bg-white rounded-xl shadow-lg border border-gray-200 space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4"
            x-data="{ preset: '{{ request('preset', 'all') }}', startDate: '{{ request('start_date') }}', endDate: '{{ request('end_date') }}' }">

            {{-- New Filter Preset Group --}}
            <div class="flex-shrink-0">
                <label for="preset" class="block text-sm font-medium text-gray-700">Pilih Periode</label>
                <select id="preset" name="preset" x-model="preset"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                    <option value="all">Seluruh tanggal</option>
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            {{-- Custom Range Dates (Hanya muncul jika preset == 'custom') --}}
            <div class="flex space-x-2 flex-shrink-0" x-show="preset == 'custom'">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Awal</label>
                    {{-- Input tidak 'required' karena hanya wajib jika preset='custom' --}}
                    <input type="date" id="start_date" name="start_date" :required="preset == 'custom'"
                        x-model="startDate" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" id="end_date" name="end_date" :required="preset == 'custom'" x-model="endDate"
                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>

            {{-- Filter Status Group --}}
            <div class="flex-shrink-0">
                <label for="status" class="block text-sm font-medium text-gray-700">Filter Status Pembelian</label>
                <select id="status" name="status"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="return" {{ request('status') == 'return' ? 'selected' : '' }}>Retur</option>
                </select>
            </div>

            {{-- Export Buttons --}}
            <div class="flex space-x-2 flex-shrink-0">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <i class='bx bx-filter-alt text-lg'></i>
                    <span>Filter</span>
                </button>


                {{-- Merge query params yang ada, termasuk preset dan status saat ini --}}
                @php
                    $exportParams = array_merge(request()->query(), [
                        'preset' => request('preset', 'all'),
                        'status' => request('status', 'all'),
                    ]);
                @endphp
                <div class="flex items-center gap-3">
                    {{-- Tombol Export PDF --}}
                    <div class="relative group">
                        <a href="{{ route('laporan.pembelian.export.pdf', $exportParams) }}" target="_blank"
                            class="flex items-center justify-center px-4 py-2 rounded-sm border border-gray-200 bg-gray-50 shadow hover:bg-gray-100 transition-all duration-200">
                            <i class='bx bxs-file-pdf text-2xl text-red-600'></i>
                        </a>
                        <span
                            class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 transition-all duration-300">
                            PDF
                        </span>
                    </div>

                    {{-- Tombol Export Excel --}}
                    <div class="relative group">
                        <a href="{{ route('laporan.pembelian.export.excel', $exportParams) }}" target="_blank"
                            class="flex items-center justify-center px-4 py-2 rounded-sm border border-gray-200 bg-gray-50 shadow hover:bg-gray-100 transition-all duration-200">
                            <i class='bx bxs-file-export text-2xl text-green-600'></i>
                        </a>
                        <span
                            class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 transition-all duration-300">
                            Excel
                        </span>
                    </div>
                </div>

            </div>
        </form>

        {{-- TABLE DATA --}}
        <div class="flex flex-col">
            <div class="overflow-x-auto shadow-lg sm:rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode Transaksi
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pemasok {{-- Ganti Supplier menjadi Pemasok --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Bayar
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($pembelians as $index => $pembelian)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ $pembelians->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d F Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $pembelian->kode_pembelian }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{-- GUNAKAN relasi 'pemasok' dan null coalescing untuk menghindari error --}}
                                    {{ $pembelian->pemasok->nama_pemasok ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    Rp{{ number_format($pembelian->total_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    {{-- Kolom Status --}}
                                    @if ($pembelian->returPembelians->isNotEmpty())
                                        <span
                                            class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class='bx bxs-x-circle mr-1'></i> Retur
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class='bx bxs-check-circle mr-1'></i> Completed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-lg bg-gray-50">
                                    <i class='bx bx-info-circle mr-1'></i> Tidak ada data pembelian yang ditemukan sesuai
                                    filter.
                                </td>
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
