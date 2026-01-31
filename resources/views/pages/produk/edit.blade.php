@extends('layouts.layout')
@section('title', 'Edit Produk')
@section('subtitle', 'Ubah informasi produk yang sudah ada')
@section('content')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">

                <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Upload Gambar --}}
                    <div class="flex items-start gap-6">
                        <div
                            class="w-32 h-32 rounded-lg overflow-hidden border border-gray-300 bg-gray-50 flex items-center justify-center">
                            <img id="preview-image"
                                src="{{ $produk->photo_produk ? asset('storage/' . $produk->photo_produk) : asset('assets/images/produk/default-produk.png') }}"
                                alt="Preview Produk" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto Produk</label>
                            <input type="file" id="photo_produk" name="photo_produk" accept="image/*"
                                class="mt-2 block w-full text-sm text-gray-700
                                   file:mr-4 file:rounded-lg file:border-0
                                   file:bg-blue-600 file:px-4 file:py-2
                                   file:text-sm file:font-medium file:text-white
                                   hover:file:bg-blue-700" />
                            <p class="text-xs text-gray-500 mt-1">Upload gambar produk (Max 2MB, format JPG/PNG).</p>
                            @error('photo_produk')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Form Input (2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Kode Produk (Disamakan dengan Create) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Unit</label>
                                <input type="text" id="prefix_input" list="prefix_list"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-2.5"
                                    placeholder="Ketik prefix baru...">
                                <datalist id="prefix_list">
                                </datalist>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Produk <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="kode_produk" name="kode_produk"
                                    value="{{ old('kode_produk', $produk->kode_produk) }}"
                                    class="mt-1 block w-full bg-gray-50 rounded-lg border-gray-300 shadow-sm text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @error('kode_produk')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Nama Produk --}}
                        <div>
                            <label for="nama_produk" class="block text-sm font-medium text-gray-700">Nama Produk <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="nama_produk" name="nama_produk"
                                value="{{ old('nama_produk', $produk->nama_produk) }}"
                                placeholder="Contoh: Mikroskop Biologi"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                      focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @error('nama_produk')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label for="kategori_id" class="block text-sm font-medium text-gray-700">Kategori <span
                                    class="text-red-500">*</span></label>
                            <select id="kategori_id" name="kategori_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                   focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ old('kategori_id', $produk->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Stok --}}
                        <div>
                            <label for="stok_produk" class="block text-sm font-medium text-gray-700">Stok Produk <span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="stok_produk" name="stok_produk"
                                value="{{ old('stok_produk', $produk->stok_produk) }}" placeholder="Contoh: 100"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                      focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @error('stok_produk')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pengingat Stok --}}
                        <div>
                            <label for="pengingat_stok" class="block text-sm font-medium text-gray-700">Batas Pengingat Stok
                                <span class="text-red-500">*</span></label>
                            <input type="number" id="pengingat_stok" name="pengingat_stok"
                                value="{{ old('pengingat_stok', $produk->pengingat_stok) }}" required min="0"
                                class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm shadow-sm
                                focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Cth: 10" />
                            @error('pengingat_stok')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Modal --}}
                        <div>
                            <label for="harga_beli" class="block text-sm font-medium text-gray-700">Modal<span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="harga_beli" name="harga_beli"
                                value="{{ old('harga_beli', $produk->harga_beli) }}" placeholder="Contoh: 1500000"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                      focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @error('harga_beli')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Harga Jual --}}
                        <div>
                            <label for="harga_jual" class="block text-sm font-medium text-gray-700">Harga Jual <span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="harga_jual" name="harga_jual"
                                value="{{ old('harga_jual', $produk->harga_jual) }}" placeholder="Contoh: 2000000"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                      focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @error('harga_jual')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="is_active" name="is_active"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                   focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="active"
                                    {{ old('is_active', $produk->is_active) == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="non_active"
                                    {{ old('is_active', $produk->is_active) == 'non_active' ? 'selected' : '' }}>Non Active
                                </option>
                            </select>
                            @error('is_active')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="deskripsi_produk" class="block text-sm font-medium text-gray-700">Deskripsi
                            Produk</label>
                        <textarea id="deskripsi_produk" name="deskripsi_produk" rows="4"
                            class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                               focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            placeholder="Tulis deskripsi produk...">{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                        @error('deskripsi_produk')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('produk.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Batal</a>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Update</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Prefix Logika
        function loadPrefixes() {
            fetch("{{ route('produk.prefixes') }}")
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('prefix_list');
                    list.innerHTML = '';
                    data.forEach(prefix => {
                        const option = document.createElement('option');
                        option.value = prefix;
                        list.appendChild(option);
                    });
                });
        }

        document.getElementById('prefix_input').addEventListener('input', function() {
            let prefix = this.value.toUpperCase();
            if (prefix.length >= 2) {
                fetch(`{{ route('produk.generateKode') }}?prefix=${prefix}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('kode_produk').value = data.kode;
                    });
            }
        });

        // Image Preview
        document.getElementById('photo_produk').addEventListener('change', function(e) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').setAttribute('src', e.target.result);
            };
            if (this.files[0]) reader.readAsDataURL(this.files[0]);
        });

        document.addEventListener('DOMContentLoaded', loadPrefixes);
    </script>
@endsection
