@extends('layouts.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}</h1>
                <p class="text-sm text-gray-500">
                    Ringkasan aktivitas penjualan berdasarkan periode
                </p>
            </div>

            {{-- Filter Periode --}}
            <form method="GET" class="mt-3 lg:mt-0 flex items-center gap-2">
                <label for="filter" class="text-sm font-medium text-gray-700">Filter:</label>
                <select name="filter" id="filter"
                    class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    onchange="this.form.submit()">
                    <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </form>
        </div>

        {{-- Card utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="bg-orange-200 text-orange-800 px-6 py-4 rounded-xl shadow-lg">
                <p class="text-sm">Total Penjualan</p>
                <p class="text-xl font-bold mt-2">Rp{{ number_format($penjualan ?? 0, 0, ',', '.') }}</p>
            </div>

            <div class="bg-teal-200 text-teal-800 px-6 py-4 rounded-xl shadow-lg">
                <p class="text-sm">Total Pembelian</p>
                <p class="text-xl font-bold mt-2">Rp{{ number_format($pembelian ?? 0, 0, ',', '.') }}</p>
            </div>

            <div class="bg-indigo-200 text-indigo-800 px-6 py-4 rounded-xl shadow-lg">
                <p class="text-sm">Total Produk</p>
                <p class="text-xl font-bold mt-2">{{ $totalProduk ?? 0 }}</p>
            </div>

            <div class="bg-green-200 text-green-800 px-6 py-4 rounded-xl shadow-lg">
                <p class="text-sm">Profit</p>
                <p class="text-xl font-bold mt-2">Rp{{ number_format($profit ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Data tambahan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-200">
                <p class="text-sm font-medium text-gray-500">Total Pelanggan</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPelanggan ?? 0 }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-200">
                <p class="text-sm font-medium text-gray-500">Total Pemasok</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPemasok ?? 0 }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-200">
                <p class="text-sm font-medium text-gray-500">Tanggal Hari Ini</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </div>
@endsection
