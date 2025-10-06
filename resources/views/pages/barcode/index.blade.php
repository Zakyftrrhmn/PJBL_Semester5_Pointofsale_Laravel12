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

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Product <span class="text-red-500">*</span></label>
            <div class="flex gap-3 items-center">
                <select x-model="selectedProduct" @change="addProduk()" class="border rounded-lg p-2 w-full">
                    <option value="">-- Pilih Produk --</option>
                    <template x-for="p in produks" :key="p.id">
                        <option :value="p.id" x-text="`${p.nama_produk} (${p.kode_produk})`"></option>
                    </template>
                </select>
            </div>
        </div>

        <template x-if="selectedProdukList.length">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-semibold text-gray-800">Produk yang akan dicetak</h3>
                    <button @click="clearProduk()" type="button" class="text-red-500 text-sm hover:underline">
                        Hapus Semua
                    </button>
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
                                    <td class="px-4 py-3 flex items-center gap-3">
                                        {{-- Perbaikan: Pastikan asset() digunakan dengan benar --}}
                                        <img :src="p.gambar ? '/storage/' + p.gambar :
                                            '{{ asset('assets/images/produk/default-produk.png') }}'"
                                            class="w-10 h-10 rounded-lg object-cover">
                                        <span x-text="p.nama_produk" class="font-medium text-gray-700"></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700" x-text="p.kode_produk ?? '-'"></td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" @click="if(p.qty > 1) p.qty--"
                                                class="p-1 border rounded-lg text-gray-600 hover:bg-gray-100">
                                                <i class='bx bx-minus'></i>
                                            </button>
                                            <span x-text="p.qty" class="font-semibold"></span>
                                            <button type="button" @click="p.qty++"
                                                class="p-1 border rounded-lg text-gray-600 hover:bg-gray-100">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeProduk(p.id)"
                                            class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200 hover:bg-gray-100">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="openGenerateModal()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-md bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-600 disabled:opacity-50">
                        </i><i class="bx bx-show"></i> Lihat Barcode
                    </button>
                    <button type="button" @click="submitPdfForm()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                        <i class='bx bx-printer'></i> Cetak ke PDF
                    </button>
                </div>

            </div>
        </template>

        <div x-cloak x-show="showModal" class="fixed inset-0 z-99999 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 **bg-gray-500 bg-opacity-75** transition-opacity" @click="showModal = false">
                </div>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Lihat Barcode
                        </h3>


                        <div class="mt-2 max-h-96 overflow-y-auto" x-html="modalContent">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>


                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Alpine.js Functionality --}}
    <script>
        function barcodeData(config) {
            return {
                selectedProduct: null,
                produks: config.produks,
                selectedProdukList: [],
                showModal: false,
                modalContent: '',
                isLoading: false,
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
                    if (!this.selectedProdukList.find(p => p.id === produk.id)) {
                        this.selectedProdukList.push({
                            ...produk,
                            qty: 1
                        });
                    }
                    this.selectedProduct = null;
                },

                removeProduk(id) {
                    this.selectedProdukList = this.selectedProdukList.filter(p => p.id !== id);
                },

                clearProduk() {
                    this.selectedProdukList = [];
                },

                async openGenerateModal() {
                    if (this.selectedProdukList.length === 0) {
                        alert('Silakan pilih produk terlebih dahulu.');
                        return;
                    }

                    this.isLoading = true;
                    this.modalContent = '<div class=\'text-center p-8\'>Loading...</div>';
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

                        this.modalContent = await response.text();

                    } catch (error) {
                        console.error(error);
                        this.modalContent = '<div class=\'text-center p-8 text-red-500\'>Gagal memuat barcode.</div>';
                    } finally {
                        this.isLoading = false;
                    }
                },

                submitPdfForm() {
                    if (this.selectedProdukList.length === 0) {
                        alert('Silakan pilih produk terlebih dahulu.');
                        return;
                    }

                    // Membuat form dinamis untuk POST request dan membuka di tab baru
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.routeCetakPdf;
                    form.target = '_blank'; // Buka di tab baru

                    // CSRF Token
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = this.csrfToken;
                    form.appendChild(tokenInput);

                    // Data produk dan jumlah
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
