@extends('layouts.layout')
@section('title', 'Cetak Barcode')
@section('subtitle', 'Pilih produk yang ingin dicetak barcodenya')
@section('content')

    <div class="space-y-6" x-data="barcodeData({
        produks: @js($produks),
        csrfToken: '{{ csrf_token() }}',
        routeGenerate: '{{ route('barcode.generate') }}',
        routeCetakPdf: '{{ route('barcode.cetak-pdf') }}'
    })">

        {{-- Form Pilih Produk --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Product <span class="text-red-500">*</span></label>
            <div class="flex gap-3 items-center">
                <select x-model="selectedProduct" @change="addProduk()" class="border rounded-lg p-2 w-full">
                    <option value="">-- Pilih Produk --</option>
                    <template x-for="p in produks" :key="p.id">
                        <option :value="p.id" x-text="`${p.nama_produk} (${p.kode_produk})`"></option>
                    </template>
                </select>
                <button @click="addAllProduk()" type="button"
                    class="whitespace-nowrap px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center gap-2">
                    <i class='bx bx-plus-circle'></i>
                    <span>Tambah Semua</span>
                </button>
            </div>
        </div>

        {{-- Tabel Produk Terpilih --}}
        <template x-if="selectedProdukList.length">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                    <h3 class="text-base font-semibold text-gray-800">
                        Produk yang akan dicetak (<span x-text="selectedProdukList.length"></span>)
                    </h3>

                    <div class="flex flex-wrap gap-2 items-center">
                        {{-- Set Global Qty --}}
                        <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg border border-blue-200">
                            <label class="text-sm font-medium text-blue-700 whitespace-nowrap">Set Qty Semua:</label>
                            <input type="number" x-model.number="globalQty" min="1" max="999"
                                class="w-20 px-2 py-1 border border-blue-300 rounded text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button @click="setGlobalQty()" type="button"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm font-medium">
                                Terapkan
                            </button>
                        </div>

                        {{-- Hapus Semua --}}
                        <button @click="clearProduk()" type="button"
                            class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 flex items-center gap-1 border border-red-200">
                            <i class='bx bx-trash'></i>
                            <span class="text-sm font-medium">Hapus Semua</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Produk</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Kode</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-700">Qty</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="p in selectedProdukList" :key="p.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img :src="p.gambar ? '/storage/' + p.gambar :
                                                '{{ asset('assets/images/produk/default-produk.png') }}'"
                                                class="w-10 h-10 rounded-lg object-cover">
                                            <span x-text="p.nama_produk" class="font-medium text-gray-700"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700" x-text="p.kode_produk ?? '-'"></td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" @click="if(p.qty > 1) p.qty--"
                                                class="p-1 border rounded-lg text-gray-600 hover:bg-gray-100 transition">
                                                <i class='bx bx-minus'></i>
                                            </button>
                                            <input type="number" x-model.number="p.qty" min="1" max="999"
                                                class="w-16 px-2 py-1 border rounded text-center font-semibold">
                                            <button type="button" @click="p.qty++"
                                                class="p-1 border rounded-lg text-gray-600 hover:bg-gray-100 transition">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeProduk(p.id)"
                                            class="p-2 border rounded-lg shadow-sm text-red-600 border-gray-200 hover:bg-red-50 transition">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openGenerateModal()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-md bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="bx bx-show"></i>
                        <span x-text="isLoading ? 'Loading...' : 'Lihat Barcode'"></span>
                    </button>
                    <button type="button" @click="submitPdfForm()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class='bx bx-printer'></i> Cetak ke PDF
                    </button>
                </div>
            </div>
        </template>

        {{-- Modal Preview Barcode --}}
        <div x-cloak x-show="showModal" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

                {{-- Overlay --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showModal = false">
                </div>

                {{-- Modal Content --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">

                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
                                Preview Barcode
                            </h3>
                            <button @click="showModal = false" type="button"
                                class="text-gray-400 hover:text-gray-600 transition">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="mt-2 max-h-[70vh] overflow-y-auto" x-html="modalContent"></div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" @click="showModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Script --}}
    <script>
        function barcodeData(config) {
            return {
                selectedProduct: null,
                produks: config.produks,
                selectedProdukList: [],
                showModal: false,
                modalContent: '',
                isLoading: false,
                globalQty: 1, // Nilai default untuk qty global
                csrfToken: config.csrfToken,
                routeGenerate: config.routeGenerate,
                routeCetakPdf: config.routeCetakPdf,

                get selectedProdukData() {
                    return this.produks.find(p => p.id == this.selectedProduct);
                },

                addProduk() {
                    if (!this.selectedProduct) return;

                    const produk = this.selectedProdukData;
                    if (!produk) return;

                    const exists = this.selectedProdukList.find(p => p.id === produk.id);

                    if (!exists) {
                        this.selectedProdukList.push({
                            ...produk,
                            qty: 1
                        });
                    } else {
                        alert('Produk sudah ada di list!');
                    }

                    this.selectedProduct = "";
                },

                addAllProduk() {
                    if (this.produks.length === 0) {
                        alert('Tidak ada produk yang tersedia.');
                        return;
                    }

                    let addedCount = 0;

                    this.produks.forEach(produk => {
                        const exists = this.selectedProdukList.find(p => p.id === produk.id);

                        if (!exists) {
                            this.selectedProdukList.push({
                                ...produk,
                                qty: 1
                            });
                            addedCount++;
                        }
                    });

                    this.selectedProduct = "";

                    if (addedCount > 0) {
                        alert(`Berhasil menambahkan ${addedCount} produk!`);
                    } else {
                        alert('Semua produk sudah ada di list.');
                    }
                },

                // METHOD BARU: Set qty global untuk semua produk
                setGlobalQty() {
                    if (this.globalQty < 1) {
                        alert('Qty minimal adalah 1!');
                        this.globalQty = 1;
                        return;
                    }

                    if (this.selectedProdukList.length === 0) {
                        alert('Tidak ada produk yang dipilih!');
                        return;
                    }

                    if (confirm(`Set qty semua produk menjadi ${this.globalQty}?`)) {
                        this.selectedProdukList.forEach(p => {
                            p.qty = this.globalQty;
                        });
                        alert(`Qty semua produk berhasil diubah menjadi ${this.globalQty}!`);
                    }
                },

                removeProduk(id) {
                    if (confirm('Hapus produk ini dari list?')) {
                        this.selectedProdukList = this.selectedProdukList.filter(p => p.id !== id);
                    }
                },

                clearProduk() {
                    if (confirm('Hapus semua produk dari list?')) {
                        this.selectedProdukList = [];
                    }
                },

                async openGenerateModal() {
                    if (this.selectedProdukList.length === 0) {
                        alert('Silakan pilih produk terlebih dahulu.');
                        return;
                    }

                    this.isLoading = true;
                    this.modalContent = `
                        <div class="text-center p-8">
                            <i class="bx bx-loader-alt bx-spin text-5xl text-indigo-500"></i>
                            <p class="mt-3 text-gray-600">Memuat barcode...</p>
                        </div>
                    `;
                    this.showModal = true;

                    const produkIds = this.selectedProdukList.map(p => p.id);
                    const jumlahData = {};
                    this.selectedProdukList.forEach(p => {
                        jumlahData[p.id] = p.qty;
                    });

                    try {
                        const response = await fetch(this.routeGenerate, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                produk_id: produkIds,
                                jumlah: jumlahData
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Gagal memuat barcode');
                        }

                        const html = await response.text();
                        this.modalContent = html;

                    } catch (error) {
                        console.error('Error:', error);
                        this.modalContent = `
                            <div class="text-center p-8">
                                <i class="bx bx-error-circle text-5xl text-red-500"></i>
                                <p class="mt-3 text-red-600 font-medium">Gagal memuat barcode</p>
                                <p class="text-sm text-gray-500 mt-2">${error.message}</p>
                            </div>
                        `;
                    } finally {
                        this.isLoading = false;
                    }
                },

                submitPdfForm() {
                    if (this.selectedProdukList.length === 0) {
                        alert('Silakan pilih produk terlebih dahulu.');
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.routeCetakPdf;
                    form.target = '_blank';

                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = this.csrfToken;
                    form.appendChild(tokenInput);

                    this.selectedProdukList.forEach(p => {
                        const produkIdInput = document.createElement('input');
                        produkIdInput.type = 'hidden';
                        produkIdInput.name = 'produk_id[]';
                        produkIdInput.value = p.id;
                        form.appendChild(produkIdInput);

                        const jumlahInput = document.createElement('input');
                        jumlahInput.type = 'hidden';
                        jumlahInput.name = `jumlah[${p.id}]`;
                        jumlahInput.value = p.qty;
                        form.appendChild(jumlahInput);
                    });

                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }
            }
        }
    </script>
@endsection
