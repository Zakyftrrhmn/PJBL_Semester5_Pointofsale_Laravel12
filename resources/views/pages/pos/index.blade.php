@extends('layouts.app')

@section('title', 'Kasir (Point of Sale)')
@section('subtitle', 'Transaksi Penjualan Baru')

@section('content')

    {{-- Script untuk Alpine.js --}}
    @php

        $produksForJs = $produks; // Asumsi $produks adalah koleksi yang sudah di-select field-field penting saja.
        $pelanggansForJs = $pelanggans; // Sama untuk pelanggan.
    @endphp

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

        <div class="flex flex-col lg:flex-row gap-6" x-data="posData({
            initialProduks: @js($produksForJs),
            initialPelanggans: @js($pelanggansForJs),
            pelangganUmumId: '{{ $pelangganUmum->id ?? '' }}'
        })">

            {{-- 1. Product Panel (Kiri) --}}
            <div class="lg:w-2/3 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sticky top-4 z-10">
                    <input type="text" x-model="searchTerm" x-ref="searchInput" @input.debounce.300ms="searchProduk"
                        placeholder="Cari produk berdasarkan nama atau kode..."
                        class="h-10 w-full rounded-lg border border-gray-200 pl-4 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    <template x-for="produk in filteredProduks" :key="produk.id">
                        <div @click="addToCart(produk)"
                            :class="{
                                'opacity-50 cursor-not-allowed': produk.stok_produk <= 0,
                                'hover:shadow-xl hover:-translate-y-1 cursor-pointer': produk.stok_produk > 0
                            }"
                            class="flex flex-col justify-between min-w-[150px] rounded-xl border border-gray-200 bg-white p-3 text-center shadow-sm transition-all duration-200 ease-in-out">

                            <div
                                class="relative w-full mb-2 overflow-hidden rounded-lg aspect-square border border-gray-100 bg-gray-50">
                                <img :src="produk.photo_produk ?
                                    '{{ asset('storage') }}/' + produk.photo_produk :
                                    '{{ asset('assets/images/produk/default-produk.png') }}'"
                                    alt="Foto Produk"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">

                                <template x-if="produk.stok_produk <= 0">
                                    <div
                                        class="absolute inset-0 bg-black/60 flex items-center justify-center text-xs font-semibold uppercase tracking-wide text-white rounded-lg">
                                        Habis
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="produk.nama_produk"
                                    :title="produk.nama_produk"></p>
                                <p class="text-xs text-gray-500 font-mono truncate" x-text="produk.kode_produk"></p>

                                <p class="font-bold text-green-600 text-sm sm:text-base leading-tight break-words"
                                    x-text="formatRupiah(produk.harga_jual)"></p>

                                <p class="text-xs font-medium mt-1 break-words"
                                    :class="produk.stok_produk <= 5 ? 'text-red-500' : 'text-gray-500'"
                                    x-text="'Stok: ' + produk.stok_produk"></p>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredProduks.length === 0">
                        <div class="col-span-full text-center py-10 text-gray-500">
                            Tidak ada produk ditemukan.
                        </div>
                    </template>
                </div>

            </div>

            {{-- 2. Cart & Payment Panel (Kanan) --}}
            <div class="lg:w-1/3 bg-white rounded-2xl border border-gray-200 shadow-lg p-5 flex flex-col h-full">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Keranjang Belanja</h3>

                {{-- Daftar Item Keranjang --}}
                <div class="flex-grow overflow-y-auto space-y-3 mb-4 min-h-[200px] max-h-[40vh]">
                    <template x-if="cart.length === 0">
                        <div class="text-center py-10 text-gray-500">
                            Keranjang kosong. Pilih produk di samping.
                        </div>
                    </template>
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="flex items-center gap-3 border-b pb-3">
                            <div class="flex-grow">
                                <p class="text-sm font-medium" x-text="item.nama_produk"></p>
                                <p class="text-xs text-gray-500" x-text="formatRupiah(item.harga_satuan)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decrementQty(index)" :disabled="item.qty <= 1"
                                    class="text-gray-600 hover:text-red-600 disabled:text-gray-300">
                                    <i class="bx bx-minus-circle text-lg"></i>
                                </button>
                                <input type="number" x-model.number="item.qty"
                                    @input="updateQty(index, $event.target.value)" min="1" :max="item.stok_produk"
                                    class="w-12 text-center text-sm border border-gray-300 rounded-lg p-1" />
                                <button @click="incrementQty(index)" :disabled="item.qty >= item.stok_produk"
                                    class="text-gray-600 hover:text-green-600 disabled:text-gray-300">
                                    <i class="bx bx-plus-circle text-lg"></i>
                                </button>
                            </div>
                            <div class="w-20 text-right">
                                <p class="text-sm font-bold text-blue-600" x-text="formatRupiah(item.subtotal)"></p>
                            </div>
                            <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700">
                                <i class="bx bx-x-circle text-lg"></i>
                            </button>
                        </div>
                    </template>
                </div>
                {{-- Akhir Daftar Item Keranjang --}}

                <hr class="mb-4">

                {{-- Ringkasan dan Pembayaran --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <p class="text-gray-600">Total Harga (Bruto)</p>
                        <p class="font-medium" x-text="formatRupiah(subtotalCart)"></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label for="diskon" class="text-sm text-gray-600 w-24">Diskon (Rp)</label>
                        <input type="number" id="diskon" x-model.number="diskon" @input="calculateTotals" min="0"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700 focus:border-blue-400" />
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold bg-blue-50 p-2 rounded-lg">
                        <p class="text-gray-800">Total Bayar</p>
                        <p class="text-blue-600" x-text="formatRupiah(totalBayar)"></p>
                    </div>

                    <div class="space-y-2">
                        <label for="pelanggan_id" class="block text-sm font-medium text-gray-700">Pelanggan</label>
                        <select id="pelanggan_id" x-model="pelanggan_id"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700 focus:border-blue-400">
                            <template x-for="p in initialPelanggans" :key="p.id">
                                <option :value="p.id" x-text="p.nama_pelanggan"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <label for="jumlah_bayar" class="text-sm text-gray-600 w-24">Bayar (Rp) <span
                                class="text-red-500">*</span></label>
                        <input type="number" id="jumlah_bayar" x-model.number="jumlahBayar" @input="calculateTotals"
                            :min="totalBayar"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700 focus:border-blue-400" />
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold p-2 rounded-lg"
                        :class="kembalian < 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'">
                        <p>Kembalian</p>
                        <p x-text="formatRupiah(kembalian)"></p>
                    </div>
                </div>

                <hr class="my-4">

                <form action="{{ route('pos.store') }}" method="POST" class="mt-auto">
                    @csrf

                    <input type="hidden" name="pelanggan_id" :value="pelanggan_id">
                    <input type="hidden" name="diskon" :value="diskon">
                    <input type="hidden" name="total_bayar" :value="totalBayar">
                    <input type="hidden" name="jumlah_bayar" :value="jumlahBayar">
                    <input type="hidden" name="kembalian" :value="kembalian">

                    {{-- Ubah 'total_harga' menjadi 'subtotalCart' di Alpine, karena 'totalHarga' tidak ada di logic Alpine Anda --}}
                    <input type="hidden" name="cart_data" :value="JSON.stringify(cart)">
                    <input type="hidden" name="total_harga" :value="subtotalCart">


                    <button type="submit" :disabled="!isReadyToPay"
                        :class="{
                            'bg-blue-600 hover:bg-blue-700': isReadyToPay,
                            'bg-gray-400 cursor-not-allowed': !isReadyToPay
                        }"
                        class="w-full inline-flex items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white shadow-sm transition duration-150 ease-in-out">
                        <i class="bx bx-wallet text-xl mr-2"></i>
                        <span x-text="buttonText"></span>
                    </button>
                </form>


            </div>
        </div>
    </div>

    {{-- Logic Alpine.js --}}
    <script>
        function posData(data) {
            return {
                // Data Statis
                allProduks: data.initialProduks,
                initialPelanggans: data.initialPelanggans,
                pelangganUmumId: data.pelangganUmumId,

                // Data Reaktif
                searchTerm: '',
                filteredProduks: data.initialProduks,
                cart: [],
                pelanggan_id: data.pelangganUmumId,
                diskon: 0,
                jumlahBayar: 0,

                // Computed Properties
                get subtotalCart() {
                    return this.cart.reduce((sum, item) => sum + (item.subtotal || 0), 0);
                },
                get totalBayar() {
                    let total = this.subtotalCart - this.diskon;
                    return total > 0 ? total : 0;
                },
                get kembalian() {
                    return this.jumlahBayar - this.totalBayar;
                },
                get isReadyToPay() {
                    return this.cart.length > 0 && this.kembalian >= 0 && this.jumlahBayar >= this.totalBayar;
                },
                get buttonText() {
                    if (this.cart.length === 0) return 'Tambah Produk Dahulu';
                    if (this.kembalian < 0) return 'Bayar Kurang ' + this.formatRupiah(Math.abs(this.kembalian));
                    return 'Selesaikan Transaksi';
                },

                init() {
                    this.calculateTotals();
                },

                formatRupiah(number) {
                    if (number === null || isNaN(number)) return 'Rp 0';
                    return 'Rp ' + Math.abs(number).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                // Logika Keranjang
                calculateTotals() {
                    this.diskon = Math.max(0, this.diskon || 0);
                    this.jumlahBayar = Math.max(0, this.jumlahBayar || 0);

                    this.cart.forEach(item => {
                        // Pastikan harga_satuan adalah angka
                        const hargaSatuan = parseFloat(item.harga_satuan) || 0;
                        item.subtotal = item.qty * hargaSatuan;
                    });
                },

                addToCart(produk) {
                    if (produk.stok_produk <= 0) {
                        alert(`Stok ${produk.nama_produk} habis!`);
                        return;
                    }

                    const existingIndex = this.cart.findIndex(item => item.id === produk.id);

                    if (existingIndex > -1) {
                        // Produk sudah ada, tambahkan kuantitas
                        const item = this.cart[existingIndex];
                        if (item.qty < produk.stok_produk) {
                            item.qty++;
                            this.calculateTotals();
                        } else {
                            alert(`Maksimal stok untuk ${produk.nama_produk} adalah ${produk.stok_produk}!`);
                        }
                    } else {
                        // Produk belum ada, tambahkan ke keranjang
                        // Menggunakan harga_jual sebagai harga_satuan
                        const hargaJual = parseFloat(produk.harga_jual) || 0;
                        this.cart.push({
                            id: produk.id,
                            nama_produk: produk.nama_produk,
                            kode_produk: produk.kode_produk,
                            harga_satuan: hargaJual,
                            stok_produk: produk.stok_produk, // Untuk validasi
                            qty: 1,
                            subtotal: hargaJual,
                        });
                        this.calculateTotals();
                    }
                },

                updateQty(index, newQty) {
                    let qty = parseInt(newQty) || 1;
                    const item = this.cart[index];

                    if (qty < 1) qty = 1;
                    if (qty > item.stok_produk) {
                        qty = item.stok_produk;
                        alert(`Maksimal stok untuk ${item.nama_produk} adalah ${item.stok_produk}!`);
                    }

                    // Gunakan $set untuk memastikan Alpine mendeteksi perubahan pada array item di cart
                    this.cart[index].qty = qty;
                    this.calculateTotals();
                },

                incrementQty(index) {
                    const item = this.cart[index];
                    if (item.qty < item.stok_produk) {
                        item.qty++;
                        this.calculateTotals();
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
                    this.cart.splice(index, 1);
                    this.calculateTotals();
                },

                // Logika Pencarian Produk (Manual / Barcode Text)
                searchProduk() {
                    const term = this.searchTerm.toLowerCase().trim();
                    if (!term) {
                        this.filteredProduks = this.allProduks;
                        return;
                    }

                    // Cek apakah input adalah kode produk (mengabaikan integrasi barcode scanner)
                    const produkByCode = this.allProduks.find(p => p.kode_produk.toLowerCase() === term);

                    if (produkByCode) {
                        // Jika kode produk cocok, tambahkan ke keranjang dan bersihkan input
                        this.addToCart(produkByCode);
                        this.searchTerm = '';
                        this.$refs.searchInput.focus();
                        this.filteredProduks = this.allProduks; // Reset filter setelah menemukan produk
                        return;
                    }


                    // Jika tidak cocok, lakukan filter berdasarkan nama/kode
                    this.filteredProduks = this.allProduks.filter(produk =>
                        (produk.nama_produk && produk.nama_produk.toLowerCase().includes(term)) ||
                        (produk.kode_produk && produk.kode_produk.toLowerCase().includes(term))
                    );
                },

                // Logika Form Submission (Dihapus dari form, dipindahkan ke button agar tidak perlu mencegah default submit)
                // submitTransaction(event) { ... }
            }
        }
    </script>
@endsection
