@extends('layouts.layout')
@section('title', 'Detail Produk')
@section('subtitle', 'Lihat informasi lengkap tentang produk')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">

                {{-- Foto Produk --}}
                <div class="flex items-start gap-6 mb-6">
                    <div
                        class="w-40 h-40 rounded-lg overflow-hidden border border-gray-300 bg-gray-50 flex items-center justify-center">
                        <img src="{{ $produk->photo_produk ? asset('storage/' . $produk->photo_produk) : asset('images/no-image.png') }}"
                            alt="Foto Produk" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ $produk->nama_produk }}</h2>
                        <p class="text-sm text-gray-500">Kode Produk: <span
                                class="font-mono">{{ $produk->kode_produk }}</span></p>
                        <p class="mt-2">
                            <span
                                class="px-2 py-1 rounded text-xs {{ $produk->is_active == 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($produk->is_active) }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Detail Produk --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Kategori</p>
                        <p class="font-medium">{{ $produk->kategori->nama_kategori ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Merek</p>
                        <p class="font-medium">{{ $produk->merek->nama_merek ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Satuan</p>
                        <p class="font-medium">{{ $produk->satuan->nama_satuan ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Stok</p>
                        <p class="font-medium">{{ $produk->stok_produk }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Harga Beli</p>
                        <p class="font-medium">Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Harga Jual</p>
                        <p class="font-medium">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="mt-6">
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="font-medium">{{ $produk->deskripsi_produk ?? '-' }}</p>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end mt-6 gap-3">
                    <a href="{{ route('produk.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Kembali</a>
                    <a href="{{ route('produk.edit', $produk->id) }}"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Edit</a>
                </div>

            </div>
        </div>
    </div>
@endsection
