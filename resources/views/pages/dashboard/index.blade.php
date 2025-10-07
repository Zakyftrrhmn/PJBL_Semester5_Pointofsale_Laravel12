@extends('layouts.layout')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan performa bisnis Anda')
@section('content')

    <div class="space-y-6">

        {{-- === HEADER DAN FILTER === --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">

            {{-- Ucapan Selamat Datang --}}
            <h1 class="text-2xl font-bold text-gray-800">
                Selamat Datang Kembali, <span class="text-blue-600">{{ Auth::user()->name }}!</span>
            </h1>

            {{-- Filter Waktu --}}
            <form action="{{ route('dashboard.index') }}" method="GET" class="mt-4 sm:mt-0">
                <div class="flex items-center gap-3">
                    <label for="filter" class="text-sm font-medium text-gray-700">Filter Data:</label>
                    <select id="filter" name="filter" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="daily" {{ $filter == 'daily' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="monthly" {{ $filter == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="yearly" {{ $filter == 'yearly' ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Keseluruhan Data</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- === KARTU WARNING STOK HAMPIR HABIS (LAMA) DIHILANGKAN UNTUK MENGGANTINYA DENGAN KARTU BARU --}}
        {{-- @if ($countStokHampirHabis > 0)
            <div role="alert" class="rounded-xl border border-red-300 bg-red-50 p-4 shadow-md">
                ...
            </div>
        @endif --}}

        {{-- === GRID KARTU METRIK TRANSAKSI (Financial) === --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

            {{-- KARTU 1: Total Penjualan (Bruto) --}}
            @include('components.dashboard-card', [
                'title' => 'Total Penjualan (Bruto)',
                'value' => 'Rp ' . number_format($totalPenjualan, 0, ',', '.'),
                'icon' => 'bx-trending-up',
                'color' => 'bg-green-100 text-green-700',
                'icon_bg' => 'bg-green-600',
            ])

            {{-- KARTU 2: Total Pembelian --}}
            @include('components.dashboard-card', [
                'title' => 'Total Pembelian',
                'value' => 'Rp ' . number_format($totalPembelian, 0, ',', '.'),
                'icon' => 'bx-package',
                'color' => 'bg-blue-100 text-blue-700',
                'icon_bg' => 'bg-blue-600',
            ])

            {{-- KARTU 3: PENJUALAN BERSIH (NET SALES) --}}
            @include('components.dashboard-card', [
                'title' => 'Penjualan Bersih',
                'value' => 'Rp ' . number_format($penjualanBersih, 0, ',', '.'),
                'icon' => 'bx-money',
                'color' => $penjualanBersih >= 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-red-100 text-red-700',
                'icon_bg' => $penjualanBersih >= 0 ? 'bg-indigo-600' : 'bg-red-600',
            ])

            {{-- KARTU 4: Total Invoice --}}
            @include('components.dashboard-card', [
                'title' => 'Jumlah Invoice',
                'value' => number_format($jumlahInvoice, 0, ',', '.') . ' Transaksi',
                'icon' => 'bx-receipt',
                'color' => 'bg-purple-100 text-purple-700',
                'icon_bg' => 'bg-purple-600',
            ])
        </div>

        {{-- === GRID KARTU METRIK RETUR & MASTER DATA === --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 lg:grid-cols-6">
            {{-- KARTU 5: Retur Penjualan --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Retur Jual',
                'value' => 'Rp ' . number_format($totalReturPenjualan, 0, ',', '.'),
                'color' => 'text-red-600',
            ])

            {{-- KARTU 6: Retur Pembelian --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Retur Beli',
                'value' => 'Rp ' . number_format($totalReturPembelian, 0, ',', '.'),
                'color' => 'text-yellow-600',
            ])

            {{-- KARTU 7: LABA BERSIH (Pindah ke kartu kecil) --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Laba Bersih',
                'value' => 'Rp ' . number_format($labaBersih, 0, ',', '.'),
                'color' => $labaBersih >= 0 ? 'text-green-600' : 'text-red-600',
            ])

            {{-- KARTU 8: Jumlah Produk --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Jumlah Produk',
                'value' => number_format($jumlahProduk, 0, ',', '.'),
                'color' => 'text-gray-600',
            ])

            {{-- KARTU 9: Jumlah Pelanggan --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Pelanggan',
                'value' => number_format($jumlahPelanggan, 0, ',', '.'),
                'color' => 'text-blue-600',
            ])

            {{-- KARTU 10: Jumlah Pemasok --}}
            @include('components.dashboard-card-sm', [
                'title' => 'Pemasok',
                'value' => number_format($jumlahPemasok, 0, ',', '.'),
                'color' => 'text-purple-600',
            ])
        </div>


        {{-- === GRID KARTU PRODUK (Top Selling & Low Stock) === --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- KARTU 1: TOP SELLING PRODUCTS --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class='bx bxs-store text-xl mr-2 text-pink-500'></i> Top Selling Products
                    </h3>
                    <span class="text-sm text-gray-500">Periode: {{ ucfirst($filter) }}</span>
                </div>

                @if ($topSellingProducts->isEmpty())
                    <p class="text-gray-500 text-center py-4">Belum ada data penjualan pada periode ini.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($topSellingProducts as $produk)
                            <li class="flex items-center justify-between py-3">
                                <div class="flex items-center space-x-3">
                                    {{-- Placeholder Gambar Produk --}}
                                    <div
                                        class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 overflow-hidden">
                                        @if ($produk->photo_produk)
                                            <img src="{{ asset('storage/' . $produk->photo_produk) }}" alt="Foto Produk"
                                                class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ asset('assets/images/produk/default-produk.png') }}" alt="Default"
                                                class="w-full h-full object-cover">
                                        @endif
                                    </div>

                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900">{{ $produk->nama_produk }}</p>
                                        <p class="text-gray-500">
                                            Harga: Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-green-600 bg-green-50 px-2.5 py-0.5 rounded-full">
                                    {{ $produk->total_terjual }} Sales
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- KARTU 2: LOW STOCK PRODUCTS --}}
            <div class="rounded-xl border border-red-300 bg-white shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-red-700 flex items-center">
                        <i class='bx bxs-error-alt text-xl mr-2'></i> Low Stock Products
                    </h3>
                    @if ($countStokHampirHabis > 0)
                        <span class="text-sm text-red-500">{{ $countStokHampirHabis }} Produk Kritis</span>
                    @else
                        <span class="text-sm text-green-500">Stok Aman</span>
                    @endif
                </div>

                @if ($countStokHampirHabis > 0)
                    <ul class="divide-y divide-gray-100">
                        @foreach ($stokHampirHabis as $produk)
                            <li class="flex items-center justify-between py-3">
                                <div class="flex items-center space-x-3">
                                    {{-- Placeholder Gambar Produk --}}
                                    <div
                                        class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 overflow-hidden">
                                        @if ($produk->photo_produk)
                                            <img src="{{ asset('storage/' . $produk->photo_produk) }}" alt="Foto Produk"
                                                class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ asset('assets/images/produk/default-produk.png') }}"
                                                alt="Default" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="text-sm">
                                        <p class="font-medium text-red-800">{{ $produk->nama_produk }}</p>
                                        <p class="text-gray-500">Kode Produk: #{{ $produk->kode_produk }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block text-sm font-bold text-red-600">
                                        {{ $produk->stok_produk }}
                                    </span>
                                    <span class="text-xs text-gray-500">Min: {{ $produk->pengingat_stok }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 text-right">
                        <a href="{{ route('produk.index') }}"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Lihat Semua Stok Kritis &rarr;
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Semua stok berada di atas batas pengingat.</p>
                @endif
            </div>
        </div>

        {{-- === BAGIAN GRAFIK PENJUALAN BERSIH === --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Tren Penjualan Bersih</h3>
                <span class="text-sm text-gray-500">
                    Filter:
                    @if ($filter == 'daily')
                        Hari Ini
                    @endif
                    @if ($filter == 'monthly')
                        Bulan Ini
                    @endif
                    @if ($filter == 'yearly')
                        Tahun Ini
                    @endif
                    @if ($filter == 'all')
                        Keseluruhan
                    @endif
                </span>
            </div>

            <canvas id="netSalesChart" height="100"></canvas>
        </div>

    </div>

    {{-- SCRIPT CHART.JS HARUS ADA DI AKHIR FILE --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Data dari Controller
        const labels = @json($chartLabels);
        const data = @json($chartData);

        const ctx = document.getElementById('netSalesChart').getContext('2d');

        // Konfigurasi Chart.js
        new Chart(ctx, {
            type: 'line', // Jenis grafik garis
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan Bersih (Rp)',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Penjualan Bersih (Rp)'
                        },
                        // Format angka Y-axis menjadi Rupiah
                        ticks: {
                            callback: function(value, index, ticks) {
                                if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'K';
                                }
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>

@endsection
