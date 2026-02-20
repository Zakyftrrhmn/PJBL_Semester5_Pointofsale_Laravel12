@extends('layouts.app')

@section('title', isset($isEditMode) && $isEditMode ? 'Edit Transaksi Penjualan' : 'Kasir (Point of Sale)')
@section('subtitle',
    isset($isEditMode) && $isEditMode
    ? 'Ubah Transaksi ' . $penjualan->kode_penjualan
    : 'Transaksi
    Penjualan Baru')

@section('content')
    @php
        $produksForJs = $produksForJs;
        $pelanggansForJs = $pelanggans;
        $initialCart = $initialCart ?? [];
        $initialPelangganId = $initialPelangganId ?? ($pelangganUmum->id ?? '');
        $initialDiskonPercent = $initialDiskonPercent ?? 0;
        $isEditMode = $isEditMode ?? false;
        $penjualanId = $penjualan->id ?? null;
    @endphp

    <div class="space-y-4" x-data="posData({
        initialProduks: @js($produksForJs),
        initialPelanggans: @js($pelanggansForJs),
        paginatedProduks: @js($produks->items()),
        pelangganUmumId: '{{ $pelangganUmum->id ?? '' }}',
        isEditMode: @js($isEditMode),
        initialCart: @js($initialCart),
        initialPelangganId: '{{ $initialPelangganId }}',
        initialDiskonPercent: @js($initialDiskonPercent),
        penjualanId: '{{ $penjualanId }}',
    })">

        {{-- Success & Error Messages --}}
        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-100 border border-green-200 flex items-center gap-3">
                <i class='bx bx-check-circle text-xl text-green-600'></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center gap-3">
                <i class='bx bx-error-circle text-xl text-red-600'></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ===================== PANEL KIRI: FORM TAMBAH PRODUK ===================== --}}
            <div class="lg:col-span-1 bg-white rounded-xl border border-gray-200 shadow-sm p-5 h-fit sticky top-4">

                {{-- HEADER --}}
                <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-center">
                            <i class='bx bx-package text-blue-600'></i>
                        </span>
                        Pilih Produk
                    </h3>

                    <span x-show="isEditMode" x-cloak
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-full text-xs font-semibold">
                        <i class='bx bx-pencil text-sm'></i> Edit Mode
                    </span>
                </div>

                {{-- BARCODE SCANNER --}}
                <div class="mb-4">
                    <div x-show="!scannerActive" x-cloak>
                        <button @click="toggleScanner()" type="button"
                            class="w-full flex items-center justify-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 text-blue-600 hover:bg-blue-100 transition-colors font-medium text-sm">
                            <i class='bx bx-barcode-reader'></i>
                            <span>Aktifkan Scanner Barcode</span>
                        </button>
                    </div>
                    <div x-show="scannerActive" x-cloak>
                        <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            <div class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                            </div>
                            <span class="text-xs font-semibold text-green-700 flex-1">Scanner Aktif</span>
                            <button @click="toggleScanner()" type="button"
                                class="p-1 hover:bg-green-100 rounded transition-colors">
                                <i class='bx bx-x text-green-600'></i>
                            </button>
                        </div>
                        <div x-show="lastScannedCode" x-cloak
                            class="mt-1.5 px-3 py-1.5 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-2">
                            <i class='bx bx-barcode text-yellow-600 text-sm'></i>
                            <span class="text-xs text-yellow-700">Terakhir: <span class="font-mono font-bold"
                                    x-text="lastScannedCode"></span></span>
                        </div>
                    </div>
                </div>

                {{-- 🔥 BUTTON BUKA MODAL --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Pilih Produk</label>
                    <button @click="openModal" type="button"
                        class="w-full flex items-center justify-between gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg px-4 py-3 font-semibold text-sm shadow-sm transition-colors">
                        <div class="flex items-center gap-2">
                            <i class='bx bx-search-alt-2 text-lg'></i>
                            <span>Cari Produk</span>
                        </div>
                        <i class='bx bx-chevron-right text-xl'></i>
                    </button>
                </div>

                {{-- INFO CARD PRODUK TERPILIH --}}
                <div x-show="tempProduk" x-cloak class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800" x-text="tempProduk?.nama_produk"></p>
                            <p class="text-xs text-gray-500 font-mono" x-text="tempProduk?.kode_produk"></p>
                        </div>
                        <span x-show="tempProduk"
                            :class="{
                                'bg-green-50 border-green-200 text-green-700': tempProduk && tempProduk.stok_produk >
                                    10,
                                'bg-amber-50 border-amber-200 text-amber-700': tempProduk && tempProduk.stok_produk >
                                    0 && tempProduk.stok_produk <= 10,
                                'bg-red-50 border-red-200 text-red-700': tempProduk && tempProduk.stok_produk <= 0
                            }"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-semibold">
                            <i class='bx bx-box text-sm'></i>
                            <span x-text="tempProduk ? 'Stok: ' + tempProduk.stok_produk : ''"></span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-blue-600" x-text="tempHarga"></span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="tempQty = Math.max(1, tempQty - 1); updateTempTotal()"
                                class="w-6 h-6 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded transition-colors">
                                <i class="bx bx-minus text-xs"></i>
                            </button>
                            <input type="number" x-model.number="tempQty" min="1" @input="updateTempTotal"
                                class="w-14 text-center text-sm border border-gray-200 rounded py-0.5 focus:ring-1 focus:ring-blue-500" />
                            <button type="button" @click="tempQty++; updateTempTotal()"
                                class="w-6 h-6 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded transition-colors">
                                <i class="bx bx-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between items-center">
                        <span class="text-xs text-gray-500">Subtotal:</span>
                        <span class="text-sm font-bold text-gray-800" x-text="tempTotal"></span>
                    </div>
                </div>

                {{-- TOMBOL TAMBAH KE KERANJANG --}}
                <button type="button" @click="addTempItemToCart" :disabled="!tempProduk"
                    :class="tempProduk ? 'bg-blue-500 hover:bg-blue-600 shadow-sm shadow-blue-200' :
                        'bg-gray-200 text-gray-400 cursor-not-allowed'"
                    class="w-full px-4 py-2.5 text-white rounded-lg font-semibold text-sm flex items-center justify-center gap-2 transition-all">
                    <i class='bx bx-plus-circle'></i>
                    Tambahkan ke Keranjang
                </button>

                <hr class="my-5 border-gray-100">

                {{-- FORM SUBMIT --}}
                <form
                    :action="isEditMode ? '{{ route('invoice.update', ['penjualan' => $penjualanId ?? ':id']) }}'.replace(':id',
                        penjualanId) : '{{ route('pos.store') }}'"
                    method="POST" @submit="handleSubmit">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="isEditMode ? 'PUT' : 'POST'" />
                    <input type="hidden" name="tanggal_penjualan" :value="tanggal_penjualan">
                    <input type="hidden" name="pelanggan_id" :value="pelanggan_id">
                    <input type="hidden" name="diskon_percent" :value="diskon_percent">
                    <input type="hidden" name="diskon_nominal" :value="diskon_trans_nominal">
                    <input type="hidden" name="total_bayar" :value="totalBayar">
                    <input type="hidden" name="cart_data" :value="JSON.stringify(cartForServer)">
                    <input type="hidden" name="total_harga" :value="subtotalAfterProductDiscounts">

                    <div class="flex gap-2">
                        <button type="button" @click="resetForm"
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold text-sm flex items-center justify-center gap-1.5 transition-colors">
                            <i class='bx bx-reset'></i>
                            Reset
                        </button>
                        <button type="submit" :disabled="!isReadyToPay"
                            :class="isReadyToPay ? 'bg-green-500 hover:bg-green-600 shadow-sm shadow-green-200' :
                                'bg-gray-200 text-gray-400 cursor-not-allowed'"
                            class="flex-1 px-4 py-2.5 text-white rounded-lg font-semibold text-sm flex items-center justify-center gap-1.5 transition-all">
                            <i class='bx bx-check-circle'></i>
                            <span x-text="isEditMode ? 'Simpan Perubahan' : 'Buat Transaksi'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ===================== PANEL KANAN: KERANJANG BELANJA ===================== --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">

                {{-- PINDAHAN DARI KIRI: INFO TRANSAKSI --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50/50 rounded-xl border border-gray-100">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Kasir</label>
                            <div
                                class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-700 truncate font-semibold">
                                {{ auth()->user()->name ?? 'admin' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">No. Invoice</label>
                            <div class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-500 font-mono truncate"
                                x-text="isEditMode ? '{{ $penjualan->kode_penjualan ?? '-' }}' : 'Auto Generate'">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                            <input type="date" x-model="tanggal_penjualan"
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Pelanggan</label>
                            <select x-model="pelanggan_id"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">- Pilih -</option>
                                <option :value="pelangganUmumId" :selected="pelanggan_id == pelangganUmumId">Umum</option>
                                <template x-for="p in initialPelanggans" :key="p.id">
                                    <option :value="p.id" :selected="p.id == pelanggan_id"
                                        x-text="p.nama_pelanggan"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- HEADER KERANJANG --}}
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <span
                            class="w-8 h-8 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center justify-center">
                            <i class='bx bx-cart text-indigo-600'></i>
                        </span>
                        Keranjang Belanja
                    </h3>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 border border-indigo-200 text-indigo-600 rounded-full text-xs font-semibold">
                        <i class='bx bx-package'></i>
                        <span x-text="cart.length + ' produk'"></span>
                    </span>
                </div>

                {{-- TABEL KERANJANG --}}
                <div class="overflow-x-auto w-full">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Produk</th>
                                <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Harga</th>
                                <th
                                    class="pb-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide min-w-[130px]">
                                    Jumlah</th>
                                <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Subtotal</th>
                                <th class="pb-3 w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- JIKA KERANJANG KOSONG --}}
                            <template x-if="cart.length === 0">
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <i class='bx bx-cart-open text-4xl'></i>
                                            <p class="text-sm">Keranjang masih kosong</p>
                                            <p class="text-xs">Klik tombol "Cari Produk" untuk menambahkan</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            {{-- ITEM KERANJANG --}}
                            <template x-for="(item, index) in cart" :key="item.id">
                                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                                    {{-- KOLOM PRODUK --}}
                                    <td class="py-3 align-top">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
                                                <img loading="lazy"
                                                    :src="item.photo_produk ? '{{ asset('storage') }}/' + item.photo_produk :
                                                        '{{ asset('assets/images/produk/default-produk.png') }}'"
                                                    :alt="item.nama_produk" class="h-full w-full object-cover">
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-800 leading-tight break-words"
                                                    x-text="item.nama_produk">
                                                </p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs text-gray-400 font-mono"
                                                        x-text="item.kode_produk"></span>
                                                    <span
                                                        :class="{
                                                            'bg-green-50 text-green-600': (item.stok_produk - item
                                                                .qty) > 10,
                                                            'bg-amber-50 text-amber-600': (item.stok_produk - item
                                                                .qty) > 0 && (item.stok_produk - item.qty) <= 10,
                                                            'bg-red-50 text-red-600': (item.stok_produk - item.qty) <= 0
                                                        }"
                                                        class="text-xs px-1.5 py-0.5 rounded font-medium">
                                                        <span x-text="'Sisa: ' + (item.stok_produk - item.qty)"></span>
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-2">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Disc
                                                        %</span>
                                                    <input type="number" step="0.1" min="0" max="100"
                                                        :value="item.diskon_item_percent"
                                                        @input.debounce.300ms="updateItemDiscount(index, $event.target.value)"
                                                        class="w-14 px-1.5 py-0.5 text-xs font-semibold border border-blue-100 rounded bg-blue-50 text-blue-600 focus:ring-1 focus:ring-blue-400 focus:outline-none" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM HARGA --}}
                                    <td class="py-3 text-right text-sm text-gray-600">
                                        <span x-text="formatRupiah(item.harga_satuan)"></span>
                                        <template x-if="item.diskon_item_percent > 0">
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                <span x-text="'Disc: ' + item.diskon_item_percent.toFixed(1) + '%'"></span>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- KOLOM JUMLAH --}}
                                    <td class="py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button @click="decrementQty(index)" :disabled="item.qty <= 1"
                                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded transition-colors disabled:opacity-40">
                                                <i class="bx bx-minus text-xs text-gray-600"></i>
                                            </button>
                                            <input type="number" min="1" :max="item.stok_produk"
                                                x-model.number="item.qty"
                                                @input.debounce.300ms="updateQty(index, $event.target.value)"
                                                class="w-12 text-center text-sm border border-gray-200 rounded py-0.5" />
                                            <button @click="incrementQty(index)" :disabled="item.qty >= item.stok_produk"
                                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded transition-colors disabled:opacity-40">
                                                <i class="bx bx-plus text-xs text-gray-600"></i>
                                            </button>
                                        </div>
                                        <template x-if="item.diskon_item_percent > 0">
                                            <div class="text-[10px] text-red-500 line-through mt-1">
                                                <span x-text="formatRupiah(item.harga_satuan * item.qty)"></span>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- KOLOM SUBTOTAL --}}
                                    <td class="py-3 text-right">
                                        <div class="text-sm font-bold text-gray-800" x-text="formatRupiah(item.subtotal)">
                                        </div>
                                        <template x-if="item.diskon_item_nominal > 0">
                                            <div class="text-[10px] text-red-500 mt-0.5">
                                                <span x-text="'Hemat: ' + formatRupiah(item.diskon_item_nominal)"></span>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- KOLOM AKSI --}}
                                    <td class="py-3 text-center">
                                        <button @click="removeFromCart(index)"
                                            class="text-xs font-semibold uppercase tracking-wide text-red-600 transition">
                                            Hapus
                                        </button>
                                    </td>

                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- RINGKASAN PEMBAYARAN --}}
                <div class="mt-5 pt-4 border-t border-gray-200 space-y-3">
                    {{-- SUB TOTAL --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Sub Total</span>
                        <span class="text-sm font-semibold text-gray-800"
                            x-text="formatRupiah(subtotalAfterProductDiscounts)"></span>
                    </div>

                    {{-- DISKON TRANSAKSI --}}
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Diskon Transaksi</span>
                            <div class="flex items-center">
                                <input type="number" x-model.number="diskon_percent"
                                    @input.debounce.300ms="calculateTotals" min="0" max="100" step="0.01"
                                    class="w-16 px-2 py-0.5 border border-gray-200 rounded-l-lg text-sm text-center focus:ring-1 focus:ring-blue-500" />
                                <span
                                    class="px-2 py-0.5 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-sm text-gray-500">%</span>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-red-500"
                            x-text="diskon_trans_nominal > 0 ? '- ' + formatRupiah(diskon_trans_nominal) : 'Rp 0'"></span>
                    </div>

                    {{-- TOTAL BAYAR --}}
                    <div
                        class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl px-4 py-3 mt-2">
                        <span class="text-sm font-bold text-blue-800">Total Bayar</span>
                        <span class="text-xl font-bold text-blue-600" x-text="formatRupiah(totalBayar)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== MODAL PENCARIAN PRODUK ===================== --}}
        <div x-show="modalOpen" x-cloak @keydown.escape.window="closeModal" class="fixed inset-0 z-99999 overflow-y-auto"
            style="display: none;">

            {{-- Backdrop --}}
            <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen p-2 sm:p-4">
                <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" @click.away="closeModal"
                    class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">

                    {{-- Modal Header --}}
                    <div
                        class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 sm:px-6 py-4 sm:py-5 flex items-center justify-between">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class='bx bx-search-alt-2 text-white text-lg sm:text-xl'></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">Pilih Produk</h3>
                                <p class="text-xs text-blue-100 hidden sm:block">Cari produk berdasarkan nama atau kode</p>
                            </div>
                        </div>
                        <button @click="closeModal"
                            class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-lg transition-all">
                            <i class='bx bx-x text-white text-2xl'></i>
                        </button>
                    </div>


                    {{-- Search Bar --}}
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
                        <div class="relative">
                            <i
                                class='bx bx-search absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg sm:text-xl'></i>
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="filterProducts"
                                placeholder="Ketik nama produk atau kode barcode..."
                                class="w-full pl-10 sm:pl-12 pr-10 sm:pr-4 py-3 sm:py-3.5 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-sm"
                                autofocus>
                            <div x-show="searchQuery" @click="searchQuery = ''; filterProducts()"
                                class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 cursor-pointer">
                                <i class='bx bx-x text-gray-400 hover:text-gray-600 text-xl'></i>
                            </div>
                        </div>

                        {{-- Counter --}}
                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                            <i class='bx bx-package'></i>
                            <span>Menampilkan <strong x-text="filteredProducts.length"></strong> produk</span>
                        </div>
                    </div>

                    {{-- Product List --}}
                    <div class="flex-1 overflow-y-auto p-3 sm:p-4" style="max-height: calc(90vh - 250px);">

                        {{-- Loading State --}}
                        <template x-if="isLoadingProducts">
                            <div class="flex flex-col items-center justify-center py-12 sm:py-16 text-gray-400">
                                <i class='bx bx-loader-alt bx-spin text-4xl sm:text-5xl mb-2'></i>
                                <p class="text-sm font-medium">Memuat produk...</p>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <template x-if="!isLoadingProducts && filteredProducts.length === 0">
                            <div class="flex flex-col items-center justify-center py-12 sm:py-16 text-gray-400">
                                <i class='bx bx-search-alt text-4xl sm:text-5xl mb-2'></i>
                                <p class="text-sm font-medium">Produk tidak ditemukan</p>
                                <p class="text-xs mt-1">Coba gunakan kata kunci lain</p>
                            </div>
                        </template>

                        {{-- Product List (Table Style) --}}
                        <div x-show="!isLoadingProducts && filteredProducts.length > 0"
                            class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                            <template x-for="produk in filteredProducts" :key="produk.id">
                                <div @click="selectFromModal(produk)"
                                    class="bg-white border border-gray-200 hover:border-blue-500 hover:bg-blue-50 rounded-lg p-3 cursor-pointer transition-colors">

                                    <div class="flex items-center gap-2 sm:gap-3">
                                        {{-- Product Image --}}
                                        <div
                                            class="w-14 h-14 sm:w-16 sm:h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                            <img loading="lazy"
                                                :src="produk.photo_produk ? '{{ asset('storage') }}/' + produk.photo_produk :
                                                    '{{ asset('assets/images/produk/default-produk.png') }}'"
                                                :alt="produk.nama_produk" class="w-full h-full object-cover">
                                        </div>

                                        {{-- Product Info --}}
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-800 text-sm truncate"
                                                x-text="produk.nama_produk"></h4>
                                            <p class="text-xs text-gray-400 font-mono mt-0.5 truncate"
                                                x-text="produk.kode_produk">
                                            </p>
                                            <div class="flex items-center gap-1.5 sm:gap-2 mt-1 flex-wrap">
                                                <span class="text-xs sm:text-sm font-bold text-blue-600"
                                                    x-text="formatRupiah(produk.harga_jual)"></span>
                                                <span class="text-xs text-gray-400 hidden sm:inline">•</span>
                                                <span
                                                    :class="{
                                                        'text-green-600': produk.stok_produk > 10,
                                                        'text-amber-600': produk.stok_produk > 0 && produk
                                                            .stok_produk <= 10,
                                                        'text-red-600': produk.stok_produk <= 0
                                                    }"
                                                    class="text-xs font-medium flex items-center gap-1">
                                                    <i class='bx bx-box text-xs'></i>
                                                    <span x-text="'Stok: ' + produk.stok_produk"></span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Arrow Icon --}}
                                        <div class="flex-shrink-0 hidden sm:block">
                                            <i class='bx bx-chevron-right text-gray-400 text-xl'></i>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-xs text-gray-500 hidden sm:block">
                            <kbd
                                class="px-2 py-1 bg-white border border-gray-300 rounded text-gray-600 font-mono">ESC</kbd>
                            untuk menutup
                        </div>
                        <button @click="closeModal"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition-colors ml-auto sm:ml-0">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== ALPINE.JS LOGIC (OPTIMIZED) ===================== --}}
    <script>
        function posData(data) {
            return {

                /* ========================================
                   DATA PROPERTIES
                ======================================== */
                allProduks: data.initialProduks || [],
                initialPelanggans: data.initialPelanggans || [],
                pelangganUmumId: data.pelangganUmumId,
                isEditMode: data.isEditMode,
                penjualanId: data.penjualanId,

                // Modal state
                modalOpen: false,
                searchQuery: '',
                filteredProducts: [],
                isLoadingProducts: false,

                // Temporary product selection
                tempProduk: null,
                tempHarga: '',
                tempQty: 1,
                tempTotal: '',

                // Cart & Transaction data
                cart: data.isEditMode ? data.initialCart : [],
                pelanggan_id: data.isEditMode ? data.initialPelangganId : data.pelangganUmumId,
                tanggal_penjualan: new Date().toISOString().split('T')[0],
                diskon_percent: data.isEditMode ? data.initialDiskonPercent : 0,
                diskon_trans_nominal: 0,

                // Barcode scanner state
                scannerActive: false,
                barcodeBuffer: '',
                lastScanTime: 0,
                scanTimeout: null,
                lastScannedCode: '',

                // Performance optimization
                formatRupiahCache: {},
                searchTimeout: null,
                calculateTimeout: null,

                /* ========================================
                   MODAL METHODS
                ======================================== */
                openModal() {
                    this.modalOpen = true;
                    this.searchQuery = '';

                    // ✅ Load initial products (limited to 50 for performance)
                    this.filteredProducts = this.allProduks.slice(0, 50);

                    document.body.style.overflow = 'hidden';

                    // Auto focus pada search input
                    this.$nextTick(() => {
                        const input = document.querySelector('[x-model="searchQuery"]');
                        if (input) input.focus();
                    });
                },

                closeModal() {
                    this.modalOpen = false;
                    this.searchQuery = '';
                    this.filteredProducts = [];
                    document.body.style.overflow = '';
                },

                filterProducts() {
                    // ✅ Clear previous timeout
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    const query = this.searchQuery.toLowerCase().trim();

                    // If empty query, show limited results
                    if (!query || query.length < 2) {
                        this.filteredProducts = this.allProduks.slice(0, 50);
                        return;
                    }

                    // ✅ Show loading state
                    this.isLoadingProducts = true;

                    // ✅ Debounce search (already handled by @input.debounce.300ms)
                    this.searchTimeout = setTimeout(() => {
                        this.filteredProducts = this.allProduks
                            .filter(p => {
                                const matchName = p.nama_produk.toLowerCase().includes(query);
                                const matchCode = p.kode_produk.toLowerCase().includes(query);
                                return matchName || matchCode;
                            })
                            .slice(0, 100); // ✅ Limit to 100 results for performance

                        this.isLoadingProducts = false;
                    }, 100);
                },

                selectFromModal(produk) {
                    this.selectProduk(produk);
                    this.closeModal();
                    this.showNotification('success', 'Produk dipilih!', produk.nama_produk);
                },

                /* ========================================
                   COMPUTED PROPERTIES
                ======================================== */
                get cartForServer() {
                    return this.cart.map(item => ({
                        id: item.id,
                        qty: item.qty,
                        diskon_percent: item.diskon_item_percent
                    }));
                },

                get subtotalAfterProductDiscounts() {
                    return this.cart.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
                },

                get totalBayar() {
                    const t = this.subtotalAfterProductDiscounts - (this.diskon_trans_nominal || 0);
                    return t > 0 ? t : 0;
                },

                get isReadyToPay() {
                    return this.cart.length > 0;
                },

                /* ========================================
                   FORMATTING METHODS (WITH CACHE)
                ======================================== */
                formatRupiah(number) {
                    // ✅ Use cache for better performance
                    const key = String(number);

                    if (this.formatRupiahCache[key]) {
                        return this.formatRupiahCache[key];
                    }

                    if (number === null || isNaN(number)) {
                        this.formatRupiahCache[key] = 'Rp 0';
                        return 'Rp 0';
                    }

                    const formatted = 'Rp ' + Math.abs(number).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    // ✅ Store in cache
                    this.formatRupiahCache[key] = formatted;

                    return formatted;
                },

                /* ========================================
                   PRODUCT SELECTION METHODS
                ======================================== */
                selectProduk(produk) {
                    this.tempProduk = produk;
                    this.tempHarga = this.formatRupiah(produk.harga_jual);
                    this.tempQty = 1;
                    this.updateTempTotal();
                },

                updateTempTotal() {
                    if (this.tempProduk) {
                        this.tempTotal = this.formatRupiah(this.tempProduk.harga_jual * this.tempQty);
                    }
                },

                addTempItemToCart() {
                    if (!this.tempProduk) return;

                    if (this.tempProduk.stok_produk <= 0) {
                        this.showNotification('error', 'Stok habis!', this.tempProduk.nama_produk);
                        return;
                    }

                    const existingIndex = this.cart.findIndex(item => item.id === this.tempProduk.id);

                    if (existingIndex > -1) {
                        const item = this.cart[existingIndex];
                        const newQty = item.qty + this.tempQty;
                        if (newQty <= item.stok_produk) {
                            item.qty = newQty;
                        } else {
                            this.showNotification('error', 'Stok maksimal tercapai', this.tempProduk.nama_produk);
                            return;
                        }
                    } else {
                        const harga = Number(this.tempProduk.harga_jual) || 0;
                        this.cart.push({
                            id: this.tempProduk.id,
                            nama_produk: this.tempProduk.nama_produk,
                            kode_produk: this.tempProduk.kode_produk,
                            photo_produk: this.tempProduk.photo_produk,
                            harga_satuan: harga,
                            stok_produk: this.tempProduk.stok_produk,
                            qty: this.tempQty,
                            diskon_item_percent: 0,
                            diskon_item_nominal: 0,
                            subtotal: harga * this.tempQty,
                        });
                    }

                    this.calculateTotals();

                    // Reset temporary data
                    this.tempProduk = null;
                    this.tempHarga = '';
                    this.tempQty = 1;
                    this.tempTotal = '';

                    this.showNotification('success', 'Ditambahkan!', 'Produk berhasil ditambahkan ke keranjang');
                },

                addProductToCartDirect(produk) {
                    if (!produk) return;

                    if (produk.stok_produk <= 0) {
                        this.showNotification('error', 'Stok habis!', produk.nama_produk);
                        return;
                    }

                    const existingIndex = this.cart.findIndex(item => item.id === produk.id);

                    if (existingIndex > -1) {
                        const item = this.cart[existingIndex];
                        if (item.qty < item.stok_produk) {
                            item.qty += 1;
                        } else {
                            this.showNotification('error', 'Stok maksimal tercapai', produk.nama_produk);
                            return;
                        }
                    } else {
                        const harga = Number(produk.harga_jual) || 0;
                        this.cart.push({
                            id: produk.id,
                            nama_produk: produk.nama_produk,
                            kode_produk: produk.kode_produk,
                            photo_produk: produk.photo_produk,
                            harga_satuan: harga,
                            stok_produk: produk.stok_produk,
                            qty: 1,
                            diskon_item_percent: 0,
                            diskon_item_nominal: 0,
                            subtotal: harga,
                        });
                    }

                    this.calculateTotals();
                    this.tempProduk = null;
                    this.tempHarga = '';
                    this.tempQty = 1;
                    this.tempTotal = '';
                },


                /* ========================================
                   BARCODE SCANNER METHODS (OPTIMIZED)
                ======================================== */
                toggleScanner() {
                    this.scannerActive = !this.scannerActive;
                    if (this.scannerActive) {
                        this.barcodeBuffer = '';
                        this.lastScanTime = 0;
                        this.lastScannedCode = '';
                        if (this.scanTimeout) {
                            clearTimeout(this.scanTimeout);
                            this.scanTimeout = null;
                        }
                        this.initBarcodeScanner();
                    } else {
                        this.removeBarcodeScanner();
                    }
                },

                initBarcodeScanner() {
                    this.barcodeBuffer = '';
                    this.lastScanTime = 0;
                    this.boundBarcodeInput = this.handleBarcodeInput.bind(this);
                    this.boundBarcodeKeyDown = this.handleBarcodeKeyDown.bind(this);

                    // ✅ Use passive listeners for better scroll performance
                    document.addEventListener('keypress', this.boundBarcodeInput, {
                        passive: true
                    });
                    document.addEventListener('keydown', this.boundBarcodeKeyDown);
                },

                removeBarcodeScanner() {
                    if (this.boundBarcodeInput) document.removeEventListener('keypress', this.boundBarcodeInput);
                    if (this.boundBarcodeKeyDown) document.removeEventListener('keydown', this.boundBarcodeKeyDown);
                    this.barcodeBuffer = '';
                    this.lastScanTime = 0;
                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                        this.scanTimeout = null;
                    }
                },

                handleBarcodeInput(e) {
                    if (!this.scannerActive) return;

                    const el = document.activeElement;
                    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT')) return;

                    const now = Date.now();
                    if (now - this.lastScanTime > 100) this.barcodeBuffer = '';

                    this.lastScanTime = now;
                    this.barcodeBuffer += e.key;

                    if (this.scanTimeout) clearTimeout(this.scanTimeout);
                    this.scanTimeout = setTimeout(() => this.processBarcodeInput(), 50);
                },

                handleBarcodeKeyDown(e) {
                    if (!this.scannerActive) return;
                    if (e.key === 'Enter' && this.barcodeBuffer.length > 0) {
                        e.preventDefault();
                        if (this.scanTimeout) clearTimeout(this.scanTimeout);
                        this.processBarcodeInput();
                    }
                },

                processBarcodeInput() {
                    const barcode = this.barcodeBuffer.trim();
                    this.barcodeBuffer = '';
                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                        this.scanTimeout = null;
                    }
                    if (barcode.length < 3) return;

                    this.lastScannedCode = barcode;

                    const produk = this.allProduks.find(p =>
                        p.kode_produk.toLowerCase() === barcode.toLowerCase()
                    );

                    if (produk) {
                        this.addProductToCartDirect(produk);
                        this.showNotification('success', 'Ditambahkan ke keranjang', produk.nama_produk);
                    } else {
                        this.showNotification('error', 'Produk tidak ditemukan', 'Barcode: ' + barcode);
                    }
                },

                showNotification(type, title, subtitle) {
                    const cfg = {
                        success: {
                            bg: 'bg-green-500',
                            icon: 'bx-check-circle'
                        },
                        error: {
                            bg: 'bg-red-500',
                            icon: 'bx-error-circle'
                        }
                    };
                    const c = cfg[type];
                    const el = document.createElement('div');
                    el.className = 'fixed top-20 right-4 ' + c.bg +
                        ' text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 transition-opacity';
                    el.innerHTML =
                        '<i class="bx ' + c.icon + ' text-lg"></i>' +
                        '<div><p class="font-semibold text-sm">' + title + '</p>' +
                        '<p class="text-xs opacity-90">' + subtitle + '</p></div>';
                    document.body.appendChild(el);
                    setTimeout(() => {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 300);
                    }, 2500);
                },

                /* ========================================
                   FORM SUBMISSION METHODS
                ======================================== */
                handleSubmit(event) {
                    if (!this.isReadyToPay) {
                        event.preventDefault();
                        this.showNotification('error', 'Keranjang kosong!', 'Tambahkan produk terlebih dahulu');
                        return false;
                    }
                    return true;
                },

                resetForm() {
                    if (confirm('Yakin ingin menghapus semua data dan reset keranjang?')) {
                        this.cart = [];
                        this.pelanggan_id = this.pelangganUmumId;
                        this.diskon_percent = 0;
                        this.diskon_trans_nominal = 0;
                        this.tempProduk = null;
                        this.tempHarga = '';
                        this.tempQty = 1;
                        this.tempTotal = '';

                        // ✅ Clear cache
                        this.formatRupiahCache = {};

                        this.showNotification('success', 'Berhasil!', 'Form telah direset');
                    }
                },

                /* ========================================
                   INITIALIZATION
                ======================================== */
                init() {
                    // ✅ Lazy load products
                    this.filteredProducts = [];

                    // Set data for edit mode
                    if (this.isEditMode) {
                        this.tanggal_penjualan = '{{ $penjualan->tanggal_penjualan ?? '' }}' || new Date().toISOString()
                            .split('T')[0];
                        this.pelanggan_id = '{{ $initialPelangganId }}';
                        this.cart = this.cart.map(item => ({
                            ...item,
                            diskon_item_percent: Number(item.diskon_item_percent || 0),
                            diskon_item_nominal: Number(item.diskon_item_nominal || 0)
                        }));
                    }

                    this.calculateTotals();
                },

                /* ========================================
                   CART CALCULATION METHODS (OPTIMIZED WITH THROTTLE)
                ======================================== */
                calculateTotals() {
                    // ✅ Clear previous timeout
                    if (this.calculateTimeout) {
                        clearTimeout(this.calculateTimeout);
                    }

                    // ✅ Throttle calculation
                    this.calculateTimeout = setTimeout(() => {
                        this.diskon_percent = Math.min(100, Math.max(0, Number(this.diskon_percent || 0)));

                        this.cart.forEach(item => {
                            const qty = Number(item.qty) || 0;
                            const harga = Number(item.harga_satuan) || 0;
                            item.diskon_item_percent = Math.min(100, Math.max(0, Number(item
                                .diskon_item_percent ||
                                0)));
                            const subtotalGross = qty * harga;
                            item.diskon_item_nominal = Math.round((item.diskon_item_percent / 100) *
                                subtotalGross);
                            item.subtotal = subtotalGross - item.diskon_item_nominal;
                        });

                        this.diskon_trans_nominal = Math.round((this.diskon_percent / 100) * this
                            .subtotalAfterProductDiscounts);

                        this.calculateTimeout = null;
                    }, 100);
                },

                /* ========================================
                   CART MANIPULATION METHODS
                ======================================== */
                updateQty(index, newQty) {
                    let qty = parseInt(newQty) || 1;
                    const item = this.cart[index];

                    if (qty < 1) qty = 1;
                    if (qty > item.stok_produk) {
                        qty = item.stok_produk;
                        this.showNotification('error', 'Stok maksimal tercapai', item.nama_produk);
                    }

                    this.cart[index].qty = qty;
                    this.calculateTotals();
                },

                updateItemDiscount(index, newPercent) {
                    let percent = parseFloat(newPercent) || 0;
                    if (percent < 0) percent = 0;
                    if (percent > 100) percent = 100;

                    this.cart[index].diskon_item_percent = percent;
                    this.calculateTotals();
                },

                incrementQty(index) {
                    const item = this.cart[index];
                    if (item.qty < item.stok_produk) {
                        item.qty++;
                        this.calculateTotals();
                    } else {
                        this.showNotification('error', 'Stok maksimal tercapai', item.nama_produk);
                    }
                },

                decrementQty(index) {
                    const item = this.cart[index];
                    if (item.qty > 1) {
                        item.qty--;
                        this.calculateTotals();
                    }
                },

                removeFromCart(index) {
                    if (confirm('Hapus produk ini dari keranjang?')) {
                        this.cart.splice(index, 1);
                        this.calculateTotals();
                        this.showNotification('success', 'Berhasil!', 'Produk dihapus dari keranjang');
                    }
                },
            };
        }
    </script>

    {{-- ===================== CUSTOM STYLES ===================== --}}
    <style>
        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ✅ Optimize animations */
        * {
            will-change: auto;
        }

        .transition-colors,
        .transition-all,
        .transition-opacity {
            will-change: auto;
        }
    </style>

@endsection
