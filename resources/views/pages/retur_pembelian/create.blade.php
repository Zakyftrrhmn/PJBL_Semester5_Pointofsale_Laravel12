@extends('layouts.layout')
@section('title', 'Buat Retur Pembelian')
@section('subtitle', 'Pilih produk yang akan dikembalikan kepada pemasok')
@section('content')

    <div class="space-y-6" x-data="returData()">

        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <form action="{{ route('retur-pembelian.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Form Header --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_retur" class="block text-sm font-medium text-gray-700">Tanggal Retur</label>
                            <input type="date" id="tanggal_retur" name="tanggal_retur"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                value="{{ old('tanggal_retur', date('Y-m-d')) }}" required>
                            @error('tanggal_retur')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pembelian_id" class="block text-sm font-medium text-gray-700">Nomor Transaksi
                                Pembelian</label>
                            <select id="pembelian_id" name="pembelian_id" x-model="pembelian_id" @change="fetchProduk"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                required>
                                <option value="">Pilih Nomor Transaksi</option>
                                @foreach ($pembelians as $pembelian)
                                    <option value="{{ $pembelian->id }}"
                                        {{ old('pembelian_id') == $pembelian->id ? 'selected' : '' }}>
                                        {{ $pembelian->kode_pembelian }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pembelian_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Detail Retur --}}
                    <h4 class="mt-6 text-base font-semibold text-gray-800 border-t pt-4">Detail Produk Retur</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="produk_id" class="block text-sm font-medium text-gray-700">Produk</label>
                            <select id="produk_id" name="produk_id" x-model="produk_id" @change="updateReturInfo"
                                :disabled="!pembelian_id || loadingProduk"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                required>
                                <option value="">Pilih Produk</option>
                                <template x-if="loadingProduk">
                                    <option disabled>Memuat produk...</option>
                                </template>
                                <template x-for="produk in listProduk" :key="produk.id">
                                    <option :value="produk.id" :data-harga="produk.harga_beli"
                                        :data-max="produk.max_retur" x-text="produk.nama_produk"></option>
                                </template>
                            </select>
                            @error('produk_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jumlah_retur" class="block text-sm font-medium text-gray-700">Jumlah Retur</label>
                            <input type="number" id="jumlah_retur" name="jumlah_retur" x-model.number="jumlah_retur"
                                :max="max_retur" min="1" :disabled="!produk_id"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Jumlah" required>
                            <p class="text-xs text-gray-500 mt-1" x-show="produk_id">Maksimal retur: <span
                                    x-text="max_retur"></span></p>
                            @error('jumlah_retur')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="harga_beli" class="block text-sm font-medium text-gray-700">Harga Beli Satuan (Saat
                                Transaksi)</label>
                            <input type="text" id="harga_beli_display" :value="formatRupiah(harga_beli)" disabled
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-gray-100">
                            <input type="hidden" name="harga_beli" :value="harga_beli">
                            @error('harga_beli')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="alasan_retur" class="block text-sm font-medium text-gray-700">Alasan Retur</label>
                        <textarea id="alasan_retur" name="alasan_retur" rows="3"
                            class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            placeholder="Jelaskan alasan retur..." required>{{ old('alasan_retur') }}</textarea>
                        @error('alasan_retur')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ringkasan Retur --}}
                    <div class="mt-6 flex justify-end border-t pt-4">
                        <div class="w-full max-w-sm space-y-2">
                            <div class="flex justify-between items-center border-t pt-2">
                                <span class="text-lg font-bold text-gray-800">PERKIRAAN NILAI RETUR:</span>
                                <span class="text-xl font-extrabold text-red-600" x-text="formatRupiah(nilaiRetur)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3 border-t pt-6">
                        <a href="{{ route('retur-pembelian.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Batal</a>
                        <button type="submit" :disabled="!produk_id || jumlah_retur < 1"
                            class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Retur
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Alpine.js Script --}}
    <script>
        function returData() {
            return {
                pembelian_id: '{{ old('pembelian_id') }}',
                produk_id: '{{ old('produk_id') }}',
                listProduk: [],
                loadingProduk: false,

                // Data Retur
                harga_beli: {{ old('harga_beli', 0) }},
                jumlah_retur: {{ old('jumlah_retur', 1) }},
                max_retur: 0,

                get nilaiRetur() {
                    return this.harga_beli * this.jumlah_retur;
                },

                fetchProduk() {
                    this.listProduk = [];
                    this.produk_id = '';
                    this.harga_beli = 0;
                    this.max_retur = 0;
                    this.jumlah_retur = 1;

                    if (!this.pembelian_id) return;

                    this.loadingProduk = true;
                    // Menggunakan route helper dan URL API untuk AJAX
                    fetch(`/admin/retur-pembelian/get-produk/${this.pembelian_id}`)
                        .then(response => response.json())
                        .then(data => {
                            this.listProduk = data;
                            this.loadingProduk = false;
                            // Coba pre-select produk jika ada di old()
                            if ('{{ old('produk_id') }}') {
                                this.produk_id = '{{ old('produk_id') }}';
                                this.$nextTick(() => this.updateReturInfo());
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching products:', error);
                            this.loadingProduk = false;
                        });
                },

                updateReturInfo() {
                    const select = document.getElementById('produk_id');
                    const selectedOption = select.options[select.selectedIndex];

                    if (selectedOption) {
                        this.harga_beli = parseFloat(selectedOption.getAttribute('data-harga'));
                        this.max_retur = parseInt(selectedOption.getAttribute('data-max'));
                        // Sesuaikan jumlah retur agar tidak melebihi maksimal
                        this.jumlah_retur = Math.min(this.jumlah_retur, this.max_retur);
                    } else {
                        this.harga_beli = 0;
                        this.max_retur = 0;
                        this.jumlah_retur = 1;
                    }
                },

                formatRupiah(angka) {
                    return 'Rp ' + (angka ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    </script>
@endsection
