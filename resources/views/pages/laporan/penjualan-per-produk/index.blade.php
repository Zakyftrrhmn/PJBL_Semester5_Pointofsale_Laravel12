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
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50 z-10">
                                No</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode Produk</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Produk</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>

                            {{-- BLOK HARGA --}}
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Satuan</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase bg-gray-100">
                                <span class="block">Harga Kotor</span>
                                <span class="text-[10px] font-normal text-gray-400">(Qty × Harga)</span>
                            </th>

                            {{-- BLOK DISKON --}}
                            <th class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                <span class="block">Disk. Item</span>
                                <span class="text-[10px] font-normal text-orange-400">(%)</span>
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                <span class="block">Disk. Item</span>
                                <span class="text-[10px] font-normal text-orange-400">(Rp)</span>
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-red-600 uppercase bg-red-50">
                                <span class="block">Disk. Transaksi</span>
                                <span class="text-[10px] font-normal text-red-400">(Alokasi)</span>
                            </th>

                            {{-- BLOK HASIL --}}
                            <th class="px-3 py-3 text-right text-xs font-semibold text-blue-600 uppercase bg-blue-50">
                                <span class="block">Subtotal Net</span>
                                <span class="text-[10px] font-normal text-blue-400">(Setelah Semua Disk.)</span>
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-orange-600 uppercase">Modal</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-green-600 uppercase">Laba</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($detailPenjualans as $index => $detail)
                            @php
                                // Hitung alokasi diskon inline di view
                                $hargaKotor = (float) $detail->qty * (float) $detail->harga_satuan;
                                $subtotalAfterItemDisc = (float) $detail->subtotal;
                                $diskonItemNominal = $hargaKotor - $subtotalAfterItemDisc;
                                $diskonItemPercent = (float) ($detail->diskon_percent ?? 0);

                                // Alokasi diskon transaksi
                                $diskonTransaksiTotal = (float) ($detail->penjualan->diskon_nominal ?? 0);
                                $totalSubtotalTransaksi = (float) ($detail->penjualan->total_harga ?? 0);
                                if ($totalSubtotalTransaksi <= 0) {
                                    $totalSubtotalTransaksi = $detail->penjualan->detailPenjualans->sum('subtotal');
                                }

                                $diskonTransaksiItem = 0;
                                if ($totalSubtotalTransaksi > 0 && $diskonTransaksiTotal > 0) {
                                    $proportion = $subtotalAfterItemDisc / $totalSubtotalTransaksi;
                                    $diskonTransaksiItem = round($diskonTransaksiTotal * $proportion, 0);
                                }

                                $subtotalNet = $subtotalAfterItemDisc - $diskonTransaksiItem;
                                $modal = (float) $detail->qty * (float) ($detail->produk->harga_beli ?? 0);
                                $laba = $subtotalNet - $modal;

                                // Flag: apakah ada diskon di row ini?
                                $hasDiskonItem = $diskonItemPercent > 0;
                                $hasDiskonTransaksi = $diskonTransaksiItem > 0;
                            @endphp
                            <tr
                                class="hover:bg-gray-50 transition-colors {{ $hasDiskonItem || $hasDiskonTransaksi ? 'bg-amber-50/30' : '' }}">
                                {{-- No --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm text-center text-gray-500 sticky left-0 bg-inherit">
                                    {{ $detailPenjualans->firstItem() + $index }}
                                </td>
                                {{-- Invoice --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm font-bold text-indigo-600">
                                    {{ $detail->penjualan->kode_penjualan }}
                                </td>
                                {{-- Tanggal --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($detail->penjualan->tanggal_penjualan)->format('d/m/Y') }}
                                </td>
                                {{-- Kode Produk --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700 font-mono">
                                    {{ $detail->produk->kode_produk ?? '-' }}
                                </td>
                                {{-- Nama Produk --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $detail->produk->nama_produk ?? '-' }}
                                </td>
                                {{-- Kategori --}}
                                <td class="px-3 py-3 text-sm text-gray-600">
                                    {{ $detail->produk->kategori->nama_kategori ?? '-' }}
                                </td>
                                {{-- Qty --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-center font-semibold text-gray-900">
                                    {{ $detail->qty }}
                                </td>

                                {{-- Harga Satuan --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                </td>

                                {{-- Harga Kotor (Qty × Harga) --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-700 bg-gray-50">
                                    @if ($hasDiskonItem || $hasDiskonTransaksi)
                                        <span
                                            class="line-through text-gray-400">{{ number_format($hargaKotor, 0, ',', '.') }}</span>
                                    @else
                                        {{ number_format($hargaKotor, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Diskon Item % --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-center bg-orange-50">
                                    @if ($hasDiskonItem)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                            {{ number_format($diskonItemPercent, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Diskon Item Rp --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm text-right text-orange-600 font-medium bg-orange-50">
                                    @if ($hasDiskonItem)
                                        <span class="text-orange-600">-
                                            {{ number_format($diskonItemNominal, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Diskon Transaksi (Alokasi Proporsional) --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm text-right text-red-600 font-medium bg-red-50">
                                    @if ($hasDiskonTransaksi)
                                        <span class="text-red-600">-
                                            {{ number_format($diskonTransaksiItem, 0, ',', '.') }}</span>
                                        {{-- Tooltip info alokasi --}}
                                        <span class="relative group">
                                            <i
                                                class="bx bx-info-circle text-xs text-red-300 cursor-pointer hover:text-red-500 ml-1"></i>
                                            <div
                                                class="absolute right-0 bottom-full mb-1.5 w-48 p-2.5 bg-gray-800 text-white text-[10px] rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-none">
                                                <p class="font-semibold mb-0.5">Alokasi Proporsional</p>
                                                <p class="text-gray-300">Disk. transaksi total dibagi ke setiap item
                                                    berdasarkan kontribusi subtotalnya.</p>
                                                <p class="mt-1 text-gray-300">
                                                    Kontribusi: {{ number_format($subtotalAfterItemDisc, 0, ',', '.') }} /
                                                    {{ number_format($totalSubtotalTransaksi, 0, ',', '.') }}
                                                    =
                                                    {{ number_format(($subtotalAfterItemDisc / max(1, $totalSubtotalTransaksi)) * 100, 1) }}%
                                                </p>
                                            </div>
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Subtotal Net (Final) --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm font-bold text-right text-blue-700 bg-blue-50">
                                    {{ number_format($subtotalNet, 0, ',', '.') }}
                                </td>

                                {{-- Modal --}}
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-right text-orange-600">
                                    {{ number_format($modal, 0, ',', '.') }}
                                </td>

                                {{-- Laba --}}
                                <td
                                    class="px-3 py-3 whitespace-nowrap text-sm font-bold text-right
                                    {{ $laba >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($laba, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-6 py-10 text-center text-gray-400 italic bg-gray-50">
                                    Tidak ada data penjualan produk yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- TFOOT: Grand Total --}}
                    @if ($detailPenjualans->count() > 0)
                        <tfoot>
                            <tr class="bg-gray-100 border-t-2 border-gray-300">
                                <td colspan="6" class="px-3 py-3 text-sm font-bold text-gray-700 text-right">
                                    GRAND TOTAL
                                </td>
                                <td class="px-3 py-3 text-sm font-bold text-center text-gray-900">
                                    {{ number_format($total_qty, 0, ',', '.') }}
                                </td>
                                <td colspan="5" class="px-3 py-3"></td>
                                {{-- Subtotal Net Total --}}
                                <td class="px-3 py-3 text-sm font-bold text-right text-blue-700 bg-blue-100">
                                    {{ number_format($total_subtotal, 0, ',', '.') }}
                                </td>
                                {{-- Modal Total --}}
                                <td class="px-3 py-3 text-sm font-bold text-right text-orange-600">
                                    {{ number_format($total_modal, 0, ',', '.') }}
                                </td>
                                {{-- Laba Total --}}
                                <td
                                    class="px-3 py-3 text-sm font-bold text-right {{ $total_laba >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ number_format($total_laba, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Pagination --}}
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
