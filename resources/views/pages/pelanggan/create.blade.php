@extends('layouts.layout')
@section('title', 'Tambah Pelanggan')
@section('subtitle', 'Isi formulir untuk menambahkan pelanggan baru')
@section('content')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <form action="{{ route('pelanggan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700">Nama Pelanggan <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            placeholder="Masukkan nama pelanggan" required>
                        @error('nama_pelanggan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telp" class="block text-sm font-medium text-gray-700">Nomor Telepon <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="telp" name="telp" value="{{ old('telp') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            placeholder="Masukkan nomor telepon" required>
                        @error('telp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            placeholder="Masukkan email pelanggan">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="photo_pelanggan" class=" text-sm font-medium text-gray-700 flex gap-x-3">
                            Foto
                            <span class="text-xs text-green-800 bg-green-200 px-2 py-1 rounded-full shadow-sm">Rekomandasi
                                Photo
                                ukuran 1:1</span>
                        </label>
                        <input type="file" id="photo_pelanggan" name="photo_pelanggan"
                            class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-lg shadow-sm cursor-pointer focus:border-blue-400 focus:ring focus:ring-blue-100">
                        @error('photo_pelanggan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('pelanggan.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</a>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
