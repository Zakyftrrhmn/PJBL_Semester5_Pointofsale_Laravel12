@extends('layouts.layout')
@section('title', 'Kategori Produk')
@section('subtitle', 'Kelola kategori produk Anda')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div id="alert-1"
                class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div class="ms-3 text-sm font-medium">
                    {{ session('success') }}
                </div>
                <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-green-200 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700"
                    data-dismiss-target="#alert-1" aria-label="Close">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

            <!-- Header -->
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">

                <!-- Pencarian & Refresh -->
                <div class="flex items-center gap-2">
                    <div class="relative w-64 sm:w-72">
                        <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                            <i class="bx bx-search text-lg"></i>
                        </span>
                        <input type="text" id="searchInput" value="{{ request('search') }}"
                            placeholder="Cari berdasarkan nama kategori..."
                            class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        <span id="loadingIcon" class="absolute top-1/2 right-3 -translate-y-1/2 text-blue-500 hidden">
                            <i class="bx bx-loader-alt bx-spin text-lg"></i>
                        </span>
                    </div>

                    <div class="relative group">
                        <!-- Tombol Refresh -->
                        <button onclick="resetFilter()"
                            class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                            <i class="bx bx-refresh text-xl"></i>
                        </button>
                        <!-- Tooltip -->
                        <span
                            class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300">
                            Reset
                        </span>
                    </div>
                </div>
                @can('kategori.create')
                    <!-- Tombol Tambah -->
                    <a href="{{ route('kategori.create') }}"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200">
                        <i class='bx bx-plus-circle'></i> Tambah Kategori
                    </a>
                @endcan
            </div>

            <!-- Tabel -->
            <div class="p-5 border-t border-gray-100 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b border-gray-100">
                                    <th class="px-5 py-3 text-left font-medium">No</th>
                                    <th class="px-5 py-3 text-left font-medium">Nama Kategori</th>
                                    @canany(['kategori.edit', 'kategori.destroy'])
                                        <th class="px-5 py-3 text-left font-medium !text-center">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-gray-100">
                                @forelse ($kategoris as $kategori)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-4 text-gray-700">
                                            {{ $loop->iteration + ($kategoris->firstItem() - 1) }}
                                        </td>
                                        <td class="px-5 py-4 text-gray-700">{{ $kategori->nama_kategori }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2 justify-center">
                                                @can('kategori.edit')
                                                    <!-- Edit -->
                                                    <a href="{{ route('kategori.edit', $kategori->id) }}"
                                                        class="relative inline-flex items-center justify-center rounded-lg p-2 text-xs shadow-sm text-gray-700 border border-gray-200">
                                                        <i class="bx bx-edit text-base"></i>
                                                        <span class="sr-only">Edit</span>
                                                    </a>
                                                @endcan
                                                @can('kategori.destroy')
                                                    <!-- Hapus -->
                                                    <button
                                                        @click="showModal = true; deleteUrl = '{{ route('kategori.destroy', $kategori->id) }}'"
                                                        class="relative inline-flex items-center justify-center rounded-lg p-2 text-xs shadow-sm text-gray-700 border border-gray-200">
                                                        <i class="bx bx-trash text-base"></i>
                                                        <span class="sr-only">Hapus</span>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-6 text-center text-gray-400 text-sm">
                                            Tidak ada data kategori.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4" id="paginationContainer">
                    {{ $kategoris->links('vendor.pagination.tailwind') }}
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

            let url = '{{ route('kategori.index') }}?page=' + page;
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
