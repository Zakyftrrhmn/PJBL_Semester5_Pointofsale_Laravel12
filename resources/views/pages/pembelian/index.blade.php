@extends('layouts.layout')
@section('title', 'Pesanan Barang Masuk')
@section('subtitle', 'Daftar semua transaksi barang masuk')
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
                    <div class="flex items-center gap-2">
                        <div class="relative w-64 sm:w-72">
                            <input type="text" id="searchInput" value="{{ request('search') }}"
                                placeholder="Cari kode/pemasok..."
                                class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                <i class="bx bx-search text-lg"></i>
                            </span>
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
                                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300">
                                Reset
                            </span>
                        </div>
                    </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-24">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody id="tableBody" class="divide-y divide-gray-200">
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
                                    Rp {{ number_format($pembelian->sisa_total_bayar, 0, ',', '.') }}
                                    @if ($pembelian->total_bayar != $pembelian->sisa_total_bayar)
                                        <p class="text-xs text-red-500 italic">
                                            (Awal: Rp {{ number_format($pembelian->total_bayar, 0, ',', '.') }})
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    @if ($pembelian->status === 'Returned')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Dikembalikan Penuh
                                        </span>
                                    @elseif ($pembelian->status === 'Partially Returned')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Sebagian Dikembalikan
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @endif

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
                                <td colspan="7" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                    data pesanan pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 px-5 pb-5" id="paginationContainer">
                {{ $pembelians->links('vendor.pagination.tailwind') }}
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

            let url = '{{ route('pesanan-pembelian.index') }}?page=' + page;
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
