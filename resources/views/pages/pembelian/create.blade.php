@extends('layouts.layout')
@section('title', 'Pembelian Baru')
@section('subtitle', 'Buat transaksi pembelian produk baru')
@section('content')

    <div class="space-y-6" x-data="pembelianData()">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <form action="{{ route('pembelian.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Form Header --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_pembelian" class="block text-sm font-medium text-gray-700">Tanggal
                                Pembelian</label>
                            <input type="date" id="tanggal_pembelian" name="tanggal_pembelian"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                value="{{ old('tanggal_pembelian', date('Y-m-d')) }}" required>
                            @error('tanggal_pembelian')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pemasok_id" class="block text-sm font-medium text-gray-700">Pemasok</label>
                            <select id="pemasok_id" name="pemasok_id"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                required>
                                <option value="">Pilih Pemasok</option>
                                @foreach ($pemasoks as $pemasok)
                                    <option value="{{ $pemasok->id }}"
                                        {{ old('pemasok_id') == $pemasok->id ? 'selected' : '' }}>
                                        {{ $pemasok->nama_pemasok }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pemasok_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h4 class="mt-6 text-base font-semibold text-gray-800 border-t pt-4">Detail Produk</h4>

                    {{-- Add Product Control --}}
                    <div class="flex items-end gap-3 mb-4 border-b pb-4">
                        <div class="flex-grow">
                            <label for="select_produk" class="block text-sm font-medium text-gray-700">Pilih Produk</label>
                            <select id="select_produk" x-model="selectedProdukId"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">--- Pilih Produk ---</option>
                                @foreach ($produks as $produk)
                                    <option value="{{ $produk->id }}" data-harga-beli="{{ $produk->harga_beli }}">
                                        {{ $produk->kode_produk }} - {{ $produk->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label for="input_jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                            <input type="number" id="input_jumlah" x-model.number="jumlah" min="1"
                                class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Jml">
                        </div>
                        <button type="button" @click="tambahProduk"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 h-11"
                            :disabled="!selectedProdukId || jumlah < 1">
                            <i class="bx bx-plus-circle text-lg mr-1"></i> Tambah
                        </button>
                    </div>

                    {{-- Product List Table --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900">
                                        Produk
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-32">
                                        Harga Beli
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-24">
                                        Jumlah
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-40">
                                        Subtotal
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-900 w-10">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(produk, index) in detailProduk" :key="index">
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <span x-text="produk.nama_produk"></span>
                                            <input type="hidden" :name="'produk[' + index + '][id]'"
                                                :value="produk.id">
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <input type="number" :name="'produk[' + index + '][harga_beli]'"
                                                x-model.number="produk.harga_beli" @change="hitungUlang"
                                                class="w-full rounded-lg border border-gray-200 p-2 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                                min="1" required>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <input type="number" :name="'produk[' + index + '][jumlah]'"
                                                x-model.number="produk.jumlah" @change="hitungUlang"
                                                class="w-full rounded-lg border border-gray-200 p-2 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                                min="1" required>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900">
                                            <span x-text="formatRupiah(produk.subtotal)"></span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <button type="button" @click="hapusProduk(index)"
                                                class="p-2 border rounded-lg shadow-sm text-red-600 border-red-200 hover:bg-red-50">
                                                <i class="bx bx-trash text-base"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="detailProduk.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">
                                        Belum ada produk yang ditambahkan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @error('produk.*.id')
                            <p class="text-xs text-red-500 p-4">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Summary & Discount/PPN --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                        <div class="md:col-span-2">
                            <p class="text-sm font-semibold text-gray-800">Ringkasan Pembayaran</p>
                            <div class="mt-2 space-y-2">
                                <div class="flex justify-between items-center border-b pb-1">
                                    <span class="text-sm text-gray-600">Total Harga Bruto:</span>
                                    <span class="text-base font-bold text-gray-800"
                                        x-text="formatRupiah(totalHargaBruto)"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="diskon" class="text-sm text-gray-600">Diskon (Rp):</label>
                                    <input type="number" id="diskon" name="diskon" x-model.number="diskon"
                                        min="0" @change="hitungTotalBayar"
                                        class="w-32 rounded-lg border border-gray-200 p-2 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                    @error('diskon')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="ppn" class="text-sm text-gray-600">PPN (Rp):</label>
                                    <input type="number" id="ppn" name="ppn" x-model.number="ppn"
                                        min="0" @change="hitungTotalBayar"
                                        class="w-32 rounded-lg border border-gray-200 p-2 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                    @error('ppn')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-between items-center border-t pt-2">
                                    <span class="text-base font-bold text-gray-800">Total Bayar:</span>
                                    <span class="text-xl font-extrabold text-indigo-600"
                                        x-text="formatRupiah(totalBayar)"></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3 border-t pt-6">
                        <a href="{{ route('pesanan-pembelian.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Batal</a>
                        <button type="submit" :disabled="detailProduk.length === 0"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Pembelian
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Alpine.js Script --}}
    <script>
        function pembelianData() {
            return {
                selectedProdukId: '',
                jumlah: 1,
                detailProduk: @json(old('produk', [])), // Isi dari old() jika ada error
                diskon: {{ old('diskon', 0) }},
                ppn: {{ old('ppn', 0) }},
                totalHargaBruto: 0,
                totalBayar: 0,

                init() {
                    // Jika ada data old produk, hitung ulang subtotalnya karena tidak disimpan di old()
                    this.detailProduk = this.detailProduk.map(item => ({
                        ...item,
                        // Pastikan harga_beli dan jumlah adalah angka
                        harga_beli: parseFloat(item.harga_beli),
                        jumlah: parseInt(item.jumlah),
                        subtotal: parseFloat(item.harga_beli) * parseInt(item.jumlah)
                    }));
                    this.hitungUlang();
                },

                tambahProduk() {
                    if (!this.selectedProdukId || this.jumlah < 1) return;

                    const select = document.getElementById('select_produk');
                    const selectedOption = select.options[select.selectedIndex];
                    const hargaBeli = parseFloat(selectedOption.getAttribute('data-harga-beli'));
                    const namaProduk = selectedOption.text.split(' - ')[1].trim();

                    // Cek duplikasi
                    const existingIndex = this.detailProduk.findIndex(p => p.id === this.selectedProdukId);

                    if (existingIndex !== -1) {
                        // Jika produk sudah ada, tambahkan jumlahnya
                        this.detailProduk[existingIndex].jumlah += this.jumlah;
                        this.detailProduk[existingIndex].subtotal = this.detailProduk[existingIndex].harga_beli * this
                            .detailProduk[existingIndex].jumlah;
                    } else {
                        // Tambahkan produk baru
                        this.detailProduk.push({
                            id: this.selectedProdukId,
                            nama_produk: namaProduk,
                            harga_beli: hargaBeli,
                            jumlah: this.jumlah,
                            subtotal: hargaBeli * this.jumlah,
                        });
                    }

                    this.hitungUlang();

                    // Reset input
                    this.selectedProdukId = '';
                    this.jumlah = 1;
                },

                hapusProduk(index) {
                    this.detailProduk.splice(index, 1);
                    this.hitungUlang();
                },

                hitungUlang() {
                    let totalBruto = 0;
                    this.detailProduk = this.detailProduk.map(produk => {
                        // Update subtotal
                        produk.subtotal = produk.harga_beli * produk.jumlah;
                        totalBruto += produk.subtotal;
                        return produk;
                    });
                    this.totalHargaBruto = totalBruto;
                    this.hitungTotalBayar();
                },

                hitungTotalBayar() {
                    let total = this.totalHargaBruto - this.diskon + this.ppn;
                    this.totalBayar = Math.max(0, total); // Pastikan total tidak minus
                },

                formatRupiah(angka) {
                    return 'Rp ' + (angka ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    </script>
@endsection
