@extends('layouts.layout')
@section('title', 'Buat Retur Penjualan')
@section('subtitle', 'Pilih produk dari transaksi penjualan yang akan diretur pelanggan')
@section('content')

    <div class="space-y-6" x-data="returData()" x-init="init()">
        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <form action="{{ route('retur-penjualan.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Header --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_retur" class="block text-sm font-medium text-gray-700">Tanggal Retur</label>
                            <input type="date" id="tanggal_retur" name="tanggal_retur"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5"
                                value="{{ old('tanggal_retur', date('Y-m-d')) }}" required>
                        </div>
                        <div>
                            <label for="penjualan_id" class="block text-sm font-medium text-gray-700">Kode Penjualan</label>
                            <select id="penjualan_id" name="penjualan_id" x-model="penjualan_id"
                                @change="fetchProdukByPenjualan(true)"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5" required>
                                <option value="">Pilih Kode Penjualan</option>
                                @foreach ($penjualans as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('penjualan_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->kode_penjualan }}
                                        ({{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-red-500" x-show="returCart.length > 0">
                                Harap selesaikan retur ini sebelum mengganti Kode Penjualan.
                            </p>
                        </div>
                    </div>

                    {{-- Detail Input Produk Retur (Sistem Keranjang) --}}
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 border-t pt-6">Tambah Produk Retur</h3>

                    <div
                        class="grid grid-cols-1 gap-4 rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm md:grid-cols-4">

                        {{-- Pilih Produk --}}
                        <div class="col-span-2">
                            <label for="produk_id_select" class="block text-sm font-medium text-gray-700">Produk</label>
                            <select id="produk_id_select" x-model="produk_id_selected" @change="updateReturInfo"
                                :disabled="loadingProduk || produkListFiltered.length === 0"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 bg-white">
                                <option value="" disabled>
                                    <span
                                        x-text="loadingProduk ? 'Memuat...' : (produkList.length === 0 ? 'Tidak ada produk untuk diretur' : 'Pilih Produk')"></span>
                                </option>
                                <template x-for="p in produkListFiltered" :key="p.id">
                                    <option :value="p.id"
                                        x-text="`${p.nama_produk} (${p.kode_produk}) - Sisa: ${p.sisa_retur} pcs`"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Jumlah Retur --}}
                        <div>
                            <label for="jumlah_retur_input" class="block text-sm font-medium text-gray-700">Jumlah
                                Retur</label>
                            <input type="number" id="jumlah_retur_input" x-model.number="jumlah_retur_input"
                                :max="max_retur" min="1" :disabled="!produk_id_selected"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5" required>
                            <p class="mt-1 text-xs text-gray-500" x-show="max_retur > 0">
                                Maksimal retur: <span x-text="max_retur"></span> pcs
                            </p>
                        </div>

                        {{-- Harga Satuan Netto (Display) --}}
                        <div class="col-span-2">
                            <label for="harga_satuan_netto_display" class="block text-sm font-medium text-gray-700">Harga
                                Satuan Netto (Refund)</label>
                            <input type="text" :value="formatRupiah(harga_satuan_netto)" id="harga_satuan_netto_display"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 bg-gray-100" readonly>
                            <p class="mt-1 text-xs"
                                :class="{ 'text-blue-500': isDiskonApplied, 'text-gray-500': !isDiskonApplied }">
                                Harga sudah memperhitungkan diskon per item & per transaksi.
                            </p>
                        </div>

                        {{-- Alasan Retur --}}
                        <div class="col-span-4">
                            <label for="alasan_retur_input" class="block text-sm font-medium text-gray-700">Alasan
                                Retur</label>
                            <textarea id="alasan_retur_input" x-model="alasan_retur_input" rows="1" :disabled="!produk_id_selected"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 resize-none" required></textarea>
                        </div>

                        {{-- Tombol Tambah --}}
                        <div class="col-span-4 flex justify-end">
                            <button type="button" @click="addToReturCart" :disabled="!canAddToReturCart()"
                                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-blue-500 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed">
                                <i class='bx bx-plus-circle mr-1'></i> Tambah Produk Retur
                            </button>
                        </div>
                    </div>


                    {{-- Tabel Daftar Produk Retur --}}
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 border-t pt-6">Daftar Produk yang Akan Diretur
                        (<span x-text="returCart.length"></span> item)</h3>

                    <div class="overflow-x-auto rounded-xl border">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Produk
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Jumlah
                                        Retur</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Harga
                                        Satuan (Netto)</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Nilai
                                        Retur</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Alasan
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-900">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in returCart" :key="item.produk_id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <span x-text="item.nama_produk"></span>
                                            <p class="text-xs text-gray-500" x-text="`Kode: ${item.kode_produk}`"></p>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <span x-text="item.jumlah_retur"></span> pcs
                                            <p class="text-xs text-gray-500" x-text="`(Maks ${item.max_qty})`"></p>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700"
                                            x-text="formatRupiah(item.harga_satuan)"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-semibold"
                                            x-text="formatRupiah(item.jumlah_retur * item.harga_satuan)"></td>
                                        <td class="whitespace-pre-wrap px-4 py-3 text-gray-700 max-w-xs"
                                            x-text="item.alasan_retur"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <button type="button" @click="removeFromReturCart(index)"
                                                class="text-red-500 hover:text-red-700">
                                                Hapus
                                            </button>
                                        </td>

                                        {{-- Hidden Inputs untuk Controller --}}
                                        <input type="hidden" :name="'retur_items[' + index + '][produk_id]'"
                                            :value="item.produk_id">
                                        <input type="hidden" :name="'retur_items[' + index + '][jumlah_retur]'"
                                            :value="item.jumlah_retur">
                                        <input type="hidden" :name="'retur_items[' + index + '][harga_satuan]'"
                                            :value="item.harga_satuan">
                                        <input type="hidden" :name="'retur_items[' + index + '][alasan_retur]'"
                                            :value="item.alasan_retur">
                                    </tr>
                                </template>
                                <tr x-show="returCart.length === 0">
                                    <td colspan="6" class="px-5 py-6 text-center text-gray-400 text-sm">Pilih Kode
                                        Penjualan, lalu tambahkan produk retur di atas.</td>
                                </tr>
                            </tbody>
                            <tfoot x-show="returCart.length > 0">
                                <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                                    <td colspan="3" class="px-4 py-3 text-right text-gray-900">Total Nilai Retur:</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-900"
                                        x-text="formatRupiah(totalNilaiRetur())"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex justify-end border-t pt-4">
                        <button type="submit" :disabled="returCart.length === 0"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-blue-300">
                            Simpan Retur (<span x-text="formatRupiah(totalNilaiRetur())"></span>)
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function returData() {
            return {
                penjualan_id: '{{ old('penjualan_id', '') }}',

                // Input fields for the selected product
                produk_id_selected: '{{ old('produk_id_selected', '') }}',
                jumlah_retur_input: parseInt('{{ old('jumlah_retur_input', 1) }}') || 1,
                alasan_retur_input: '{{ old('alasan_retur_input', '') }}',

                // Data for the currently selected product
                produkList: [], // All returable products for selected Penjualan (fetched)
                max_retur: 0,
                harga_satuan_netto: 0,
                isDiskonApplied: false,
                loadingProduk: false,

                // The cart/list of items to be returned
                returCart: [],

                // Computed property to filter the dropdown list
                get produkListFiltered() {
                    const cartIds = this.returCart.map(item => item.produk_id);
                    // Filter out products already in the cart
                    return this.produkList.filter(p => !cartIds.includes(p.id));
                },

                init() {
                    if ('{{ old('penjualan_id', '') }}') {
                        this.fetchProdukByPenjualan(false);
                    }
                    // Load any old input into the cart if necessary (complex, so we skip for simplicity and rely on session('error') reload)
                },

                fetchProdukByPenjualan(reset = true) {
                    if (!this.penjualan_id) {
                        this.produkList = [];
                        this.returCart = []; // Clear cart when penjualan changes
                        this.updateReturInfo();
                        return;
                    }
                    this.loadingProduk = true;
                    this.returCart = []; // Clear cart on new transaction selection

                    fetch(`{{ route('retur-penjualan.get-produk') }}?penjualan_id=${this.penjualan_id}`)
                        .then(r => r.json())
                        .then(data => {
                            this.produkList = data;
                            this.loadingProduk = false;

                            if (reset) {
                                this.produk_id_selected = '';
                            }
                            this.updateReturInfo();
                        })
                        .catch(() => {
                            this.loadingProduk = false;
                            alert('Gagal memuat produk dari transaksi penjualan!');
                        });
                },

                updateReturInfo() {
                    const p = this.produkList.find(p => p.id === this.produk_id_selected);
                    if (p) {
                        this.harga_satuan_netto = parseFloat(p.harga_satuan); // Final refund price
                        this.max_retur = parseInt(p.sisa_retur);
                        this.isDiskonApplied = p.diskon_diterapkan ?? false;

                        // Adjust jumlah_retur to be within bounds
                        this.jumlah_retur_input = Math.min(this.jumlah_retur_input, this.max_retur);
                        this.jumlah_retur_input = Math.max(this.jumlah_retur_input, 1);
                    } else {
                        this.harga_satuan_netto = 0;
                        this.max_retur = 0;
                        this.isDiskonApplied = false;
                    }
                },

                canAddToReturCart() {
                    return this.produk_id_selected &&
                        this.jumlah_retur_input > 0 &&
                        this.jumlah_retur_input <= this.max_retur &&
                        this.alasan_retur_input.trim() !== '' &&
                        this.harga_satuan_netto > 0;
                },

                addToReturCart() {
                    if (!this.canAddToReturCart()) {
                        alert(
                            'Harap lengkapi semua data retur dengan benar (Produk, Jumlah, Alasan) dan perhatikan batas maksimal retur.'
                        );
                        return;
                    }

                    const selectedProduct = this.produkList.find(p => p.id === this.produk_id_selected);

                    this.returCart.push({
                        produk_id: this.produk_id_selected,
                        nama_produk: selectedProduct.nama_produk,
                        kode_produk: selectedProduct.kode_produk,
                        jumlah_retur: this.jumlah_retur_input,
                        alasan_retur: this.alasan_retur_input,
                        harga_satuan: this.harga_satuan_netto, // The final refund price
                        max_qty: this.max_retur,
                    });

                    // Reset selection for new input
                    this.produk_id_selected = '';
                    this.jumlah_retur_input = 1;
                    this.alasan_retur_input = '';
                    this.max_retur = 0;
                    this.harga_satuan_netto = 0;
                    this.isDiskonApplied = false;
                },

                removeFromReturCart(index) {
                    this.returCart.splice(index, 1);
                    // Re-evaluate current selected product in case it's now available again
                    this.updateReturInfo();
                },

                totalNilaiRetur() {
                    return this.returCart.reduce((total, item) => total + (item.jumlah_retur * item.harga_satuan), 0);
                },

                formatRupiah(v) {
                    return 'Rp ' + (parseFloat(v) || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
            }
        }
    </script>

@endsection
