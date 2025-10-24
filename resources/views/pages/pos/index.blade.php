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
        // Data inisialisasi tambahan untuk edit mode
        $initialCart = $initialCart ?? [];
        $initialPelangganId = $initialPelangganId ?? ($pelangganUmum->id ?? '');
        $initialDiskonPercent = $initialDiskonPercent ?? 0;
        $initialJumlahBayar = $initialJumlahBayar ?? null;
        $isEditMode = $isEditMode ?? false;
        $penjualanId = $penjualan->id ?? null;
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
            pelangganUmumId: '{{ $pelangganUmum->id ?? '' }}',
            // Data untuk Edit Mode
            isEditMode: @js($isEditMode),
            initialCart: @js($initialCart),
            initialPelangganId: '{{ $initialPelangganId }}',
            initialDiskonPercent: @js($initialDiskonPercent),
            initialJumlahBayar: @js($initialJumlahBayar),
            penjualanId: '{{ $penjualanId }}',
        })">

            {{-- Product Panel (Kiri) --}}
            <div class="lg:w-2/3 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sticky top-4 z-10">
                    <form action="{{ route('pos.index') }}" method="GET" class="flex items-center gap-2">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" x-ref="searchInput"
                                @keydown.enter="searchProduk" placeholder="Cari produk berdasarkan nama atau kode..."
                                class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                <i class="bx bx-search text-lg"></i>
                            </span>
                        </div>
                        <button type="submit" class="hidden">Cari</button>
                        <div class="relative group">
                            <a href="{{ route('pos.index') }}"
                                class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                                <i class="bx bx-refresh text-xl"></i>
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Daftar Produk --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    @forelse ($produks as $produk)
                        <div @click="addToCart({{ json_encode($produk) }})"
                            :class="{
                                'opacity-50 cursor-not-allowed': {{ $produk['stok_produk'] }} <= 0,
                                'hover:shadow-xl hover:-translate-y-1 cursor-pointer': {{ $produk['stok_produk'] }} > 0
                            }"
                            class="flex flex-col justify-between min-w-[150px] rounded-xl border border-gray-200 bg-white p-3 text-center shadow-sm transition-all duration-200 ease-in-out">

                            <div
                                class="relative w-full mb-2 overflow-hidden rounded-lg aspect-square border border-gray-100 bg-gray-50">
                                <img src="{{ $produk['photo_produk'] ? asset('storage') . '/' . $produk['photo_produk'] : asset('assets/images/produk/default-produk.png') }}"
                                    alt="Foto Produk"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">

                                @if ($produk['stok_produk'] <= 0)
                                    <div
                                        class="absolute inset-0 bg-black/60 flex items-center justify-center text-xs font-semibold uppercase tracking-wide text-white rounded-lg">
                                        Habis
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-800 truncate"
                                    title="{{ $produk['nama_produk'] }}">
                                    {{ $produk['nama_produk'] }}</p>
                                <p class="text-xs text-gray-500 font-mono truncate">{{ $produk['kode_produk'] }}</p>

                                <p class="font-bold text-green-600 text-sm sm:text-base leading-tight break-words"
                                    x-text="formatRupiah({{ $produk['harga_jual'] }})"></p>

                                <p
                                    class="text-xs font-medium mt-1 break-words {{ $produk['stok_produk'] <= 5 ? 'text-red-500' : 'text-gray-500' }}">
                                    Stok: {{ $produk['stok_produk'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-gray-500">
                            Tidak ada produk ditemukan.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $produks->links('vendor.pagination.tailwind') }}
                </div>

            </div>

            {{-- Cart & Payment Panel (Kanan) --}}
            <div class="lg:w-1/3 bg-white rounded-2xl border border-gray-200 shadow-lg p-5 flex flex-col h-full">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Keranjang Belanja</h3>

                {{-- Daftar Item Keranjang --}}
                <div class="flex-grow overflow-y-auto space-y-3 mb-4 min-h-[150px] max-h-[40vh] p-2">
                    <template x-if="cart.length === 0">
                        <div class="text-center py-10 text-gray-500 text-sm">
                            Keranjang kosong. Tambahkan produk.
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="border-b last:border-b-0 pb-3 last:pb-0 pt-1 relative group">

                            <div class="flex justify-between items-start mb-1">
                                <p class="text-sm font-semibold text-gray-800 pr-6" x-text="item.nama_produk"></p>
                                <button @click="removeFromCart(index)"
                                    class="text-red-400 hover:text-red-600 transition duration-150 p-1 -mt-1 -mr-1 absolute top-0 right-0"
                                    title="Hapus Item">
                                    <i class="bx bx-x text-xl"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-3 gap-2 items-end">

                                <div>
                                    <label class="block text-[10px] text-gray-500 font-medium">QTY</label>
                                    <div class="flex items-center">
                                        <button @click="decrementQty(index)" :disabled="item.qty <= 1"
                                            class="text-gray-500 hover:text-red-500 disabled:text-gray-300 p-0.5">
                                            <i class="bx bx-minus text-sm"></i>
                                        </button>
                                        <input type="number" min="1" :max="item.stok_produk"
                                            x-model.number="item.qty" @input="updateQty(index, $event.target.value)"
                                            class="w-8 text-center text-xs border-y border-gray-300 p-0.5 font-medium focus:ring-blue-500 focus:border-blue-500" />
                                        <button @click="incrementQty(index)" :disabled="item.qty >= item.stok_produk"
                                            class="text-gray-500 hover:text-green-500 disabled:text-gray-300 p-0.5">
                                            <i class="bx bx-plus text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] text-gray-500 font-medium">DISKON (%)</label>
                                    <input type="number" min="0" max="100" step="0.01"
                                        class="w-full text-xs rounded-md border border-gray-300 p-1 text-center focus:ring-blue-500 focus:border-blue-500"
                                        x-model.number="item.diskon_percent" @input="calculateTotals" />
                                </div>

                                <div class="text-right">
                                    <label class="block text-[10px] text-gray-500 font-medium">SUBTOTAL</label>
                                    <p class="text-sm font-extrabold text-blue-600" x-text="formatRupiah(item.subtotal)">
                                    </p>
                                </div>
                            </div>

                            <div class="text-right mt-1">
                                <p class="text-[10px] text-gray-500 mr-2 inline"
                                    x-text="'@ ' + formatRupiah(item.harga_satuan)"></p>

                                <p class="text-[10px] text-red-500 line-through inline" x-show="item.diskon_percent > 0"
                                    x-text="formatRupiah(item.qty * item.harga_satuan)">
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
                <hr class="mb-4">

                {{-- Ringkasan dan Pembayaran --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <p class="text-gray-600">Total Harga (setelah diskon produk)</p>
                        <p class="font-medium" x-text="formatRupiah(subtotalAfterProductDiscounts)"></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label for="diskon_percent" class="text-sm text-gray-600 w-36">Diskon Transaksi (%)</label>
                        <input type="number" id="diskon_percent" x-model.number="diskon_percent" @input="calculateTotals"
                            min="0" max="100" step="0.01"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700 focus:border-blue-400" />
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <p class="text-gray-600">Diskon Transaksi (Rp)</p>
                        <p class="font-medium" x-text="formatRupiah(diskon_trans_nominal)"></p>
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold bg-blue-50 p-2 rounded-lg">
                        <p class="text-gray-800">Total Bayar</p>
                        <p class="text-blue-600" x-text="formatRupiah(totalBayar)"></p>
                    </div>

                    <div class="space-y-2">
                        <label for="pelanggan_id" class="block text-sm font-medium text-gray-700">Pelanggan</label>
                        <select id="pelanggan_id" x-model="pelanggan_id"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700 focus:border-blue-400">
                            <option value="">Umum</option>
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

                <form
                    :action="isEditMode ? '{{ route('invoice.update', ['penjualan' => $penjualanId ?? ':id']) }}'.replace(':id',
                        penjualanId) : '{{ route('pos.store') }}'"
                    method="POST" class="mt-auto">
                    @csrf

                    <template x-if="isEditMode">
                        @method('PUT')
                    </template>

                    <input type="hidden" name="pelanggan_id" :value="pelanggan_id">
                    <input type="hidden" name="diskon_percent" :value="diskon_percent">
                    <input type="hidden" name="diskon_nominal" :value="diskon_trans_nominal">
                    <input type="hidden" name="total_bayar" :value="totalBayar">
                    <input type="hidden" name="jumlah_bayar" :value="jumlahBayar">
                    <input type="hidden" name="kembalian" :value="kembalian">
                    <input type="hidden" name="cart_data" :value="JSON.stringify(cartForServer)">
                    <input type="hidden" name="total_harga" :value="subtotalAfterProductDiscounts">

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
                allProduks: data.initialProduks,
                initialPelanggans: data.initialPelanggans,
                pelangganUmumId: data.pelangganUmumId,

                isEditMode: data.isEditMode,
                penjualanId: data.penjualanId,
                initialCart: data.initialCart,
                initialPelangganId: data.initialPelangganId,
                initialDiskonPercent: data.initialDiskonPercent,
                initialJumlahBayar: data.initialJumlahBayar,

                searchTerm: '',
                filteredProduks: data.initialProduks,
                // Inisialisasi cart, pelanggan_id, diskon_percent, dan jumlahBayar dari data edit jika ada
                cart: data.isEditMode ? data.initialCart : [],
                pelanggan_id: data.isEditMode ? data.initialPelangganId : data.pelangganUmumId,

                // Diskon transaksi (persen)
                diskon_percent: data.isEditMode ? data.initialDiskonPercent :
                0, // Nominal diskon transaksi (hanya untuk tampilan)
                diskon_trans_nominal: 0,

                jumlahBayar: data.isEditMode ? data.initialJumlahBayar : null,
                // cartForServer adalah versi cart yang dikirim ke server (menyertakan diskon_percent per item)
                get cartForServer() {
                    return this.cart.map(item => ({
                        id: item.id,
                        qty: item.qty,
                        diskon_percent: Number(item.diskon_percent || 0)
                    }));
                },

                get subtotalAfterProductDiscounts() {
                    return this.cart.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
                },

                get totalBayar() {
                    let total = this.subtotalAfterProductDiscounts - (this.diskon_trans_nominal || 0);
                    return total > 0 ? total : 0;
                },

                get kembalian() {
                    if (this.jumlahBayar === null || this.jumlahBayar === '') {
                        return 0;
                    }
                    return this.jumlahBayar - this.totalBayar;
                },

                get isReadyToPay() {
                    return this.cart.length > 0 && this.kembalian >= 0 && this.jumlahBayar >= this.totalBayar;
                },

                get buttonText() {
                    if (this.cart.length === 0) return 'Tambah Produk Dahulu';
                    if (this.kembalian < 0) return 'Bayar Kurang ' + this.formatRupiah(Math.abs(this.kembalian));
                    return this.isEditMode ? 'Simpan Perubahan Transaksi' : 'Selesaikan Transaksi'; // Perubahan teks
                },

                init() {
                    this.calculateTotals();
                },

                formatRupiah(number) {
                    if (number === null || isNaN(number)) return 'Rp 0';
                    // tampilkan tanpa desimal
                    return 'Rp ' + Math.abs(number).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                calculateTotals() {
                    // Validasi sederhana
                    this.diskon_percent = Math.max(0, Number(this.diskon_percent || 0));
                    if (this.diskon_percent > 100) this.diskon_percent = 100;

                    this.cart.forEach(item => {
                        const hargaSatuan = Number(item.harga_satuan) || 0;
                        const qty = Number(item.qty) || 0;
                        const subtotalGross = qty * hargaSatuan;

                        // diskon per-item (persen)
                        item.diskon_percent = Math.max(0, Number(item.diskon_percent || 0));
                        if (item.diskon_percent > 100) item.diskon_percent = 100;

                        item.diskon_nominal = Math.round((item.diskon_percent / 100) * subtotalGross);
                        item.subtotal = Math.round(subtotalGross - item.diskon_nominal);
                        if (item.subtotal < 0) item.subtotal = 0;
                    });

                    // diskon transaksi nominal (berlaku setelah diskon produk)
                    const subtotal = this.subtotalAfterProductDiscounts;
                    this.diskon_trans_nominal = Math.round((this.diskon_percent / 100) * subtotal);

                    // jumlah bayar minimal & kembalian dihitung via getter
                    if (this.jumlahBayar !== null && this.jumlahBayar !== '') {
                        this.jumlahBayar = Math.max(0, Number(this.jumlahBayar || 0));
                    }
                },

                addToCart(produk) {
                    if (produk.stok_produk <= 0) {
                        alert(`Stok ${produk.nama_produk} habis!`);
                        return;
                    }

                    const existingIndex = this.cart.findIndex(item => item.id === produk.id);

                    if (existingIndex > -1) {
                        const item = this.cart[existingIndex];
                        if (item.qty < produk.stok_produk) {
                            item.qty++;
                            this.calculateTotals();
                        } else {
                            alert(`Maksimal stok untuk ${produk.nama_produk} adalah ${produk.stok_produk}!`);
                        }
                    } else {
                        const hargaJual = Number(produk.harga_jual) || 0;
                        // Ketika menambah produk BARU di edit mode, stok produk yang digunakan adalah STOK AKTUAL SAAT INI
                        // Stok aktual produk di `produk` sudah dihitung saat controller menginisialisasi $produksForJs
                        this.cart.push({
                            id: produk.id,
                            nama_produk: produk.nama_produk,
                            kode_produk: produk.kode_produk,
                            harga_satuan: hargaJual,
                            stok_produk: produk.stok_produk,
                            qty: 1,
                            subtotal: hargaJual,
                            diskon_percent: 0,
                            diskon_nominal: 0,
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

                searchProduk(event) {
                    const term = this.$refs.searchInput.value.toLowerCase().trim();

                    const produkByCode = this.allProduks.find(p => p.kode_produk.toLowerCase() === term);

                    if (produkByCode) {
                        this.addToCart(produkByCode);
                        this.$refs.searchInput.value = '';
                        this.searchTerm = '';
                        event.preventDefault();
                        return;
                    }
                },
            }
        }
    </script>
@endsection
