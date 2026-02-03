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
                    <div class="flex items-center gap-2 w-full max-w-xs">
                        <div class="relative flex-1">
                            <input type="text" id="searchInput" placeholder="Cari Kode Transaksi / Pelanggan"
                                value="{{ request('search') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-400 focus:ring-blue-100 pr-10">
                            <span id="loadingIcon" class="absolute top-1/2 right-3 -translate-y-1/2 text-blue-500 hidden">
                                <i class="bx bx-loader-alt bx-spin text-lg"></i>
                            </span>
                        </div>

                        <div class="relative group">
                            <button onclick="resetFilter()"
                                class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                                <i class="bx bx-refresh text-xl"></i>
                            </button>
                            <span
                                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300 whitespace-nowrap">
                                Reset
                            </span>
                        </div>
                    </div>
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
                                @canany(['invoice.show', 'invoice.edit', 'invoice.destroy'])
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center gap-2">

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
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Tidak ada data transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" id="paginationContainer">
                    {{ $penjualans->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let searchTimeout;

        // Live search
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            document.getElementById('loadingIcon').classList.remove('hidden');

            searchTimeout = setTimeout(() => {
                loadData();
            }, 300);
        });

        // Reset filter
        function resetFilter() {
            document.getElementById('searchInput').value = '';
            loadData();
        }

        // Load data dengan AJAX
        function loadData(page = 1) {
            const search = document.getElementById('searchInput').value;

            let url = '{{ route('invoice.index') }}?page=' + page;
            if (search) url += '&search=' + search;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
                    document.getElementById('paginationContainer').innerHTML = doc.getElementById('paginationContainer')
                        .innerHTML;
                    document.getElementById('loadingIcon').classList.add('hidden');

                    // Attach pagination events
                    document.querySelectorAll('#paginationContainer a').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = new URL(this.href);
                            const page = url.searchParams.get('page');
                            loadData(page);
                        });
                    });
                });
        }

        // Initial pagination events
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#paginationContainer a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page');
                    loadData(page);
                });
            });
        });
    </script>
@endpush
