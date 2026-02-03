@extends('layouts.layout')
@section('title', 'Produk')
@section('subtitle', 'Kelola produk Anda')
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
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <div class="relative w-64 sm:w-72">
                                <input type="text" id="searchInput" value="{{ request('search') }}"
                                    placeholder="Cari produk..."
                                    class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                    <i class="bx bx-search text-lg"></i>
                                </span>
                                <span id="loadingIcon"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-blue-500 hidden">
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

                        <div class="flex items-center gap-2">
                            <select id="kategoriSelect"
                                class="h-10 w-40 rounded-lg border border-gray-200 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    @can('produk.export')
                        <div class="relative group">
                            <a href="{{ route('produk.export.pdf') }}"
                                class="flex items-center justify-center w-8 h-8 rounded-sm border border-gray-200 bg-gray-50 shadow hover:bg-gray-100">
                                <i class='bx bxs-file-pdf text-2xl text-red-600'></i>
                            </a>
                            <span
                                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 transition-all duration-300">PDF</span>
                        </div>

                        <div class="relative group">
                            <a href="{{ route('produk.export.excel') }}"
                                class="flex items-center justify-center w-8 h-8 rounded-sm border border-gray-200 bg-gray-50 shadow hover:bg-gray-100">
                                <i class='bx bxs-file-export text-2xl text-green-600'></i>
                            </a>
                            <span
                                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 transition-all duration-300">Excel</span>
                        </div>
                    @endcan

                    @can('produk.create')
                        <a href="{{ route('produk.create') }}"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            <i class='bx bx-plus-circle'></i> Tambah Produk
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Tabel -->
            <div class="p-5 border-t border-gray-100 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b border-gray-100 text-sm">
                                    <th class="px-3 py-3 text-left font-medium w-12">No</th>
                                    <th class="px-3 py-3 text-left font-medium w-28">Kode</th>
                                    <th class="px-3 py-3 text-left font-medium">Info Produk</th>
                                    <th class="px-3 py-3 text-center font-medium w-20">Stok</th>
                                    <th class="px-3 py-3 text-right font-medium w-28">Modal</th>
                                    <th class="px-3 py-3 text-right font-medium w-28">Jual</th>
                                    @canany(['produk.show', 'produk.edit', 'produk.destroy'])
                                        <th class="px-3 py-3 text-center font-medium w-24">Aksi</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-gray-100">
                                @forelse ($produks as $produk)
                                    <tr class="hover:bg-gray-50 transition text-sm">
                                        <td class="px-3 py-3">
                                            {{ $loop->iteration + ($produks->firstItem() - 1) }}
                                        </td>
                                        <td class="px-3 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">
                                            {{ $produk->kode_produk }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-3 min-w-[220px]">
                                                <div class="w-11 h-11 rounded-md overflow-hidden bg-gray-100 shrink-0">
                                                    @if ($produk->photo_produk)
                                                        <img src="{{ asset('storage/' . $produk->photo_produk) }}"
                                                            class="w-full h-full object-cover">
                                                    @else
                                                        <img src="{{ asset('assets/images/produk/default-produk.png') }}"
                                                            class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                                <div class="flex flex-col leading-tight">
                                                    <span
                                                        class="font-semibold text-black">{{ $produk->nama_produk }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ $produk->kategori->nama_kategori }}</span>
                                                    <span class="text-xs mt-1">
                                                        @if ($produk->is_active === 'active')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                                                <span
                                                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                                Active
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                                Non Active
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="font-semibold">{{ $produk->stok_produk }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-right text-gray-600 whitespace-nowrap">
                                            Rp {{ number_format($produk->harga_modal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold text-green-600 whitespace-nowrap">
                                            Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 flex justify-center gap-2">
                                            @can('produk.show')
                                                <a href="{{ route('produk.show', $produk->id) }}"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            @endcan
                                            @can('produk.edit')
                                                <a href="{{ route('produk.edit', $produk->id) }}"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            @endcan
                                            @can('produk.destroy')
                                                <button
                                                    @click="showModal = true; deleteUrl = '{{ route('produk.destroy', $produk->id) }}'"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                            data produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4" id="paginationContainer">
                    {{ $produks->links('vendor.pagination.tailwind') }}
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

        // Filter kategori
        document.getElementById('kategoriSelect').addEventListener('change', function() {
            loadData();
        });

        // Reset filter
        function resetFilter() {
            document.getElementById('searchInput').value = '';
            document.getElementById('kategoriSelect').value = '';
            loadData();
        }

        // Load data dengan AJAX
        function loadData(page = 1) {
            const search = document.getElementById('searchInput').value;
            const kategori = document.getElementById('kategoriSelect').value;

            let url = '{{ route('produk.index') }}?page=' + page;
            if (search) url += '&search=' + search;
            if (kategori) url += '&kategori_id=' + kategori;

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
