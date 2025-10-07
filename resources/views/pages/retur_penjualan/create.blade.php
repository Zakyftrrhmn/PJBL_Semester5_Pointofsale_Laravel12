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
                        </div>
                    </div>

                    {{-- Detail --}}
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 border-t pt-6">Detail Retur</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="produk_id" class="block text-sm font-medium text-gray-700">Produk</label>
                            <select id="produk_id" name="produk_id" x-model="produk_id" @change="updateReturInfo"
                                :disabled="loadingProduk || produkList.length === 0"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 bg-white" required>
                                <option value="">
                                    <span x-text="loadingProduk ? 'Memuat...' : 'Pilih Produk'"></span>
                                </option>
                                <template x-for="p in produkList" :key="p.id">
                                    <option :value="p.id"
                                        x-text="`${p.nama_produk} (${p.kode_produk}) - Sisa: ${p.sisa_retur} pcs`"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="jumlah_retur" class="block text-sm font-medium text-gray-700">Jumlah Retur</label>
                            <input type="number" id="jumlah_retur" name="jumlah_retur" x-model.number="jumlah_retur"
                                :max="max_retur" min="1"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5" required>
                            <p class="mt-1 text-xs text-gray-500" x-show="max_retur > 0">Maksimal retur:
                                <span x-text="max_retur"></span> pcs
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="harga_satuan" class="block text-sm font-medium text-gray-700">Harga Satuan
                                Netto</label>
                            <input type="text" :value="formatRupiah(harga_satuan)"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 bg-gray-50" readonly>
                            <input type="hidden" name="harga_satuan" x-model="harga_satuan">

                            <p class="mt-1 text-xs"
                                :class="{ 'text-blue-500': isDiskonApplied, 'text-gray-500': !isDiskonApplied }">
                                <span x-show="isDiskonApplied">Harga sudah disesuaikan dengan diskon transaksi.</span>
                                <span x-show="!isDiskonApplied && harga_satuan > 0">Tidak ada diskon transaksi.</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Nilai Retur</label>
                            <input type="text" :value="formatRupiah(jumlah_retur * harga_satuan)"
                                class="mt-1 w-full rounded-lg border border-green-300 p-2.5 text-green-700 font-bold bg-green-50"
                                readonly>
                        </div>
                    </div>

                    <div>
                        <label for="alasan_retur" class="block text-sm font-medium text-gray-700">Alasan Retur</label>
                        <textarea id="alasan_retur" name="alasan_retur" rows="3"
                            class="mt-1 w-full rounded-lg border border-gray-200 p-2.5" required>{{ old('alasan_retur') }}</textarea>
                    </div>

                    <div class="flex justify-end border-t pt-4">
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Simpan Retur
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
                produk_id: '{{ old('produk_id', '') }}',
                produkList: [],
                harga_satuan: parseFloat('{{ old('harga_satuan', 0) }}') || 0,
                jumlah_retur: parseInt('{{ old('jumlah_retur', 1) }}') || 1,
                max_retur: 0,
                isDiskonApplied: false,
                loadingProduk: false,

                init() {
                    // Pastikan Alpine menjalankan fetch setelah render
                    if (this.penjualan_id) {
                        this.fetchProdukByPenjualan(false);
                    }
                },

                fetchProdukByPenjualan(reset = true) {
                    if (!this.penjualan_id) return;
                    this.loadingProduk = true;

                    fetch(`{{ route('retur-penjualan.get-produk') }}?penjualan_id=${this.penjualan_id}`)
                        .then(r => r.json())
                        .then(data => {
                            this.produkList = data;
                            this.loadingProduk = false;

                            if (!reset && this.produkList.find(p => p.id === this.produk_id)) {
                                this.updateReturInfo();
                            } else {
                                this.produk_id = '';
                                this.updateReturInfo();
                            }
                        })
                        .catch(() => {
                            this.loadingProduk = false;
                            alert('Gagal memuat produk dari transaksi penjualan!');
                        });
                },

                updateReturInfo() {
                    const p = this.produkList.find(p => p.id === this.produk_id);
                    if (p) {
                        this.harga_satuan = parseFloat(p.harga_satuan);
                        this.max_retur = parseInt(p.sisa_retur);
                        this.isDiskonApplied = p.diskon_diterapkan ?? false;
                        this.jumlah_retur = Math.min(this.jumlah_retur, this.max_retur);
                        this.jumlah_retur = Math.max(this.jumlah_retur, 1);
                    } else {
                        this.harga_satuan = 0;
                        this.max_retur = 0;
                        this.isDiskonApplied = false;
                    }
                },

                formatRupiah(v) {
                    return 'Rp ' + (parseFloat(v) || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
            }
        }
    </script>

@endsection
