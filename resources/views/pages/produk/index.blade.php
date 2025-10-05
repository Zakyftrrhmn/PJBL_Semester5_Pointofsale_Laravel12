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
                    <form action="{{ route('produk.index') }}" method="GET" class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <div class="relative w-64 sm:w-72">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari produk..."
                                    class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                    <i class="bx bx-search text-lg"></i>
                                </span>
                            </div>

                            <div class="relative group">
                                <a href="{{ route('produk.index') }}"
                                    class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                                    <i class="bx bx-refresh text-xl"></i>
                                </a>
                                <span
                                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300">
                                    Reset
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <select name="kategori_id" onchange="this.form.submit()"
                                class="h-10 w-40 rounded-lg border border-gray-200 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="merek_id" onchange="this.form.submit()"
                                class="h-10 w-40 rounded-lg border border-gray-200 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua Merek</option>
                                @foreach ($mereks as $merek)
                                    <option value="{{ $merek->id }}"
                                        {{ request('merek_id') == $merek->id ? 'selected' : '' }}>
                                        {{ $merek->nama_merek }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
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

                    <a href="{{ route('produk.create') }}"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        <i class='bx bx-plus-circle'></i> Tambah Produk
                    </a>
                </div>
            </div>

            <!-- Tabel -->
            <div class="p-5 border-t border-gray-100 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b border-gray-100">
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">No</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Kode Produk</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Produk</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Status</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Kategori</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Merek</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Satuan</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Stok</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Pengingat Stok</th>
                                    <th class="px-5 py-3 text-left font-medium  whitespace-nowrap">Harga Jual</th>
                                    <th class="px-5 py-3 text-center font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($produks as $produk)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            {{ $loop->iteration + ($produks->firstItem() - 1) }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $produk->kode_produk }}</td>
                                        <td class="px-5 py-4 text-gray-700  whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-100">
                                                    @if ($produk->photo_produk)
                                                        <img src="{{ asset('storage/' . $produk->photo_produk) }}"
                                                            alt="Foto Produk" class="w-full h-full object-cover">
                                                    @else
                                                        <img src="{{ asset('assets/images/produk/default-produk.png') }}"
                                                            alt="Default" class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-black whitespace-nowrap">
                                                        {{ $produk->nama_produk }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4  whitespace-nowrap">
                                            @if ($produk->is_active === 'active')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-sm">
                                                    <i class="bx bxs-circle text-[6px]"></i> Active
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-sm">
                                                    <i class="bx bxs-circle text-[6px]"></i> Non Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4  whitespace-nowrap">{{ $produk->kategori->nama_kategori }}
                                        </td>
                                        <td class="px-5 py-4  whitespace-nowrap">{{ $produk->merek->nama_merek }}</td>
                                        <td class="px-5 py-4  whitespace-nowrap">{{ $produk->satuan->nama_satuan }}</td>
                                        <td class="px-5 py-4 !text-center whitespace-nowrap">{{ $produk->stok_produk }}
                                        </td>
                                        <td class="px-5 py-4 !text-center whitespace-nowrap">{{ $produk->pengingat_stok }}
                                        </td>

                                        <td class="px-5 py-4  whitespace-nowrap">Rp
                                            {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>

                                        <td class="px-5 py-4 flex justify-center gap-2">
                                            <!-- View -->
                                            <a href="{{ route('produk.show', $produk->id) }}"
                                                class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                <i class="bx bx-show text-base"></i>
                                            </a>
                                            <!-- Edit -->
                                            <a href="{{ route('produk.edit', $produk->id) }}"
                                                class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                <i class="bx bx-edit text-base"></i>
                                            </a>
                                            <!-- Delete -->
                                            <form action="{{ route('produk.destroy', $produk->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200">
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada
                                            data produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $produks->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>
    </div>
@endsection
