@extends('layouts.layout')
@section('title', 'Tambah Kategori')
@section('subtitle', 'Isi formulir untuk menambahkan kategori baru')
@section('content')

    <div class="space-y-6">

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <!-- Form -->
            <div class="p-5 sm:p-6">
                <form action="{{ route('kategori.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nama_kategori" class="block text-sm font-medium text-gray-700">
                            Nama Kategori
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 sm:text-sm p-2.5"
                            placeholder="Masukkan nama kategori" required>
                        @error('nama_kategori')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('kategori.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
