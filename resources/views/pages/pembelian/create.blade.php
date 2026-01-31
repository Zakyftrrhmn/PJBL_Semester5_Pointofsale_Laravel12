    @extends('layouts.layout')

    @section('title', 'Barang Masuk')
    @section('subtitle', 'Buat Barang Masuk baru')

    @section('content')
        <div class="space-y-6" x-data="pembelianData()">

            <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
            <style>
                .ts-control {
                    border-radius: 0.5rem !important;
                    padding: 0.6rem !important;
                    border: 1px solid #e5e7eb !important;
                }

                .ts-wrapper.single .ts-control {
                    background-image: none !important;
                }

                .ts-dropdown {
                    border-radius: 0.5rem !important;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
                }
            </style>

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
                                    Barang Masuk</label>
                                <input type="date" id="tanggal_pembelian" name="tanggal_pembelian"
                                    class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                    value="{{ old('tanggal_pembelian', date('Y-m-d')) }}" required>
                                @error('tanggal_pembelian')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="pemasok_id" class="block text-sm font-medium text-gray-700">Pemasok</label>
                                <select id="pemasok_id" name="pemasok_id" required>
                                    <option value="">Pilih Pemasok</option>
                                    @foreach ($pemasoks as $pemasok)
                                        <option value="{{ $pemasok->id }}"
                                            {{ old('pemasok_id') == $pemasok->id ? 'selected' : '' }}>
                                            {{ $pemasok->nama_pemasok }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pemasok_id')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <h4 class="mt-6 text-base font-semibold text-gray-800 border-t pt-4">Detail Produk</h4>

                        {{-- Add Product Control --}}
                        <div class="flex items-end gap-3 mb-4 border-b pb-4">
                            <div class="flex-grow">
                                <label for="select_produk" class="block text-sm font-medium text-gray-700">Pilih
                                    Produk</label>
                                <select id="select_produk" placeholder="Cari Kode atau Nama Produk...">
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
                                    class="mt-1 w-full rounded-lg border border-gray-200 p-2.5 text-sm text-gray-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
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
                                        <th class="px-4 py-3 text-left font-medium text-gray-900">Produk</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 w-32">Harga Beli</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 w-24">Jumlah</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 w-40">Subtotal</th>
                                        <th class="px-4 py-3 text-center font-medium text-gray-900 w-10">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(produk, index) in detailProduk" :key="index">
                                        <tr>
                                            <td class="px-4 py-3 text-gray-700">
                                                <span x-text="produk.nama_produk"></span>
                                                <input type="hidden" :name="'produk[' + index + '][id]'"
                                                    :value="produk.id">
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                <div class="flex flex-col">
                                                    <input type="number" :name="'produk[' + index + '][harga_beli]'"
                                                        x-model.number="produk.harga_beli" readonly
                                                        class="w-full rounded-lg border border-gray-200 p-2 text-sm bg-gray-100">
                                                    <span class="text-[10px] text-gray-500 italic">Harga dikunci dari Master
                                                        Produk</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                <input type="number" :name="'produk[' + index + '][jumlah]'"
                                                    x-model.number="produk.jumlah" @input="hitungUlang"
                                                    class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:ring-2 focus:ring-blue-100"
                                                    min="1" required>
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-gray-900"
                                                x-text="formatRupiah(produk.subtotal)"></td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" @click="hapusProduk(index)"
                                                    class="p-2 border rounded-lg text-red-600 border-red-200 hover:bg-red-50">
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="detailProduk.length === 0">
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm italic">
                                            Belum ada produk yang ditambahkan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Summary --}}
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
                                        <label class="text-sm text-gray-600">Diskon (Rp):</label>
                                        <input type="number" name="diskon" x-model.number="diskon" min="0"
                                            @input="validasiDiskon"
                                            class="w-32 rounded-lg border border-gray-200 p-2 text-sm text-right">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <label class="text-sm text-gray-600">PPN (Rp):</label>
                                        <input type="number" name="ppn" x-model.number="ppn" min="0"
                                            @input="hitungTotalBayar"
                                            class="w-32 rounded-lg border border-gray-200 p-2 text-sm text-right">
                                    </div>
                                    <div class="flex justify-between items-center border-t pt-2">
                                        <span class="text-base font-bold text-gray-800">Total Bayar:</span>
                                        <span class="text-xl font-extrabold text-indigo-600"
                                            x-text="formatRupiah(totalBayar)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex justify-end gap-3 border-t pt-6">
                            <a href="{{ route('pesanan-pembelian.index') }}"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                            <button type="submit" :disabled="detailProduk.length === 0"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50">
                                Simpan Pembelian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

        <script>
            function pembelianData() {
                return {
                    selectedProdukId: '',
                    jumlah: 1,
                    detailProduk: @json(old('produk', [])),
                    diskon: {{ old('diskon', 0) }},
                    ppn: {{ old('ppn', 0) }},
                    totalHargaBruto: 0,
                    totalBayar: 0,

                    // Variabel instance TomSelect
                    instancePemasok: null,
                    instanceProduk: null,

                    init() {
                        // 1. Inisialisasi Tom Select Pemasok
                        this.instancePemasok = new TomSelect("#pemasok_id", {
                            create: false
                        });

                        // 2. Inisialisasi Tom Select Produk
                        this.instanceProduk = new TomSelect("#select_produk", {
                            create: false,
                            onChange: (value) => {
                                this.selectedProdukId = value; // Sinkronkan ke Alpine
                            }
                        });

                        // 3. Re-map data lama jika ada error validasi
                        if (this.detailProduk.length > 0) {
                            this.detailProduk = this.detailProduk.map(p => ({
                                ...p,
                                harga_beli: parseFloat(p.harga_beli),
                                jumlah: parseInt(p.jumlah),
                                subtotal: parseFloat(p.harga_beli) * parseInt(p.jumlah)
                            }));
                        }
                        this.hitungUlang();
                    },

                    tambahProduk() {
                        if (!this.selectedProdukId || this.jumlah < 1) return;

                        // Ambil data dari select asli lewat instance TomSelect
                        const selectElement = document.getElementById('select_produk');
                        const selectedOption = selectElement.options[selectElement.selectedIndex];

                        const hargaBeli = parseFloat(selectedOption.getAttribute('data-harga-beli'));
                        const namaProduk = selectedOption.text.split(' - ')[1].trim();

                        const existingIndex = this.detailProduk.findIndex(p => p.id === this.selectedProdukId);

                        if (existingIndex !== -1) {
                            this.detailProduk[existingIndex].jumlah += this.jumlah;
                        } else {
                            this.detailProduk.push({
                                id: this.selectedProdukId,
                                nama_produk: namaProduk,
                                harga_beli: hargaBeli,
                                jumlah: this.jumlah,
                                subtotal: hargaBeli * this.jumlah
                            });
                        }

                        this.hitungUlang();

                        // Reset TomSelect & Input
                        this.instanceProduk.clear();
                        this.selectedProdukId = '';
                        this.jumlah = 1;
                    },

                    hapusProduk(index) {
                        this.detailProduk.splice(index, 1);
                        this.hitungUlang();
                    },

                    hitungUlang() {
                        let bruto = 0;
                        this.detailProduk.forEach(p => {
                            p.subtotal = p.harga_beli * p.jumlah;
                            bruto += p.subtotal;
                        });
                        this.totalHargaBruto = bruto;
                        this.hitungTotalBayar();
                    },

                    hitungTotalBayar() {
                        this.totalBayar = Math.max(0, this.totalHargaBruto - this.diskon + this.ppn);
                    },

                    validasiDiskon() {
                        if (this.diskon < 0) this.diskon = 0;
                        if (this.diskon > this.totalHargaBruto) this.diskon = this.totalHargaBruto;
                        this.hitungTotalBayar();
                    },

                    formatRupiah(angka) {
                        return 'Rp ' + (angka ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        </script>
    @endsection
