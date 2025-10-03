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
                                src="{{ $produk->photo_produk ? asset('storage/' . $produk->photo_produk) : asset('images/no-image.png') }}"
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

                        {{-- Merek --}}
                        <div>
                            <label for="merek_id" class="block text-sm font-medium text-gray-700">Merek <span
                                    class="text-red-500">*</span></label>
                            <select id="merek_id" name="merek_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                   focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih Merek</option>
                                @foreach ($mereks as $merek)
                                    <option value="{{ $merek->id }}"
                                        {{ old('merek_id', $produk->merek_id) == $merek->id ? 'selected' : '' }}>
                                        {{ $merek->nama_merek }}
                                    </option>
                                @endforeach
                            </select>
                            @error('merek_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Satuan --}}
                        <div>
                            <label for="satuan_id" class="block text-sm font-medium text-gray-700">Satuan <span
                                    class="text-red-500">*</span></label>
                            <select id="satuan_id" name="satuan_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm
                                   focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih Satuan</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->id }}"
                                        {{ old('satuan_id', $produk->satuan_id) == $satuan->id ? 'selected' : '' }}>
                                        {{ $satuan->nama_satuan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('satuan_id')
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

                        {{-- Harga Beli --}}
                        <div>
                            <label for="harga_beli" class="block text-sm font-medium text-gray-700">Harga Beli <span
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
                                <p class="text-xs text-gray-500">
                                    Pilih <span class="font-semibold text-green-600">Active</span> untuk menampilkan data,
                                    atau <span class="font-semibold text-red-600">Non Active</span> agar data tidak
                                    ditampilkan.
                                </p>
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

    {{-- Preview Gambar --}}
    <script>
        document.getElementById('photo_produk').addEventListener('change', function(e) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').setAttribute('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        });
    </script>

@endsection
