@extends('layouts.layout')
@section('title', 'Tambah Rekening Bank')
@section('subtitle', 'Isi formulir untuk menambahkan daftar rekening baru')
@section('content')

    <div class="space-y-6">

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <!-- Form -->
            <div class="p-5 sm:p-6">
                <form action="{{ route('rekeningBank.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nama_bank" class="block text-sm font-medium text-gray-700">
                            Nama Bank
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_bank" name="nama_bank" value="{{ old('nama_bank') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 sm:text-sm p-2.5"
                            placeholder="Masukkan nama bank" required>
                        @error('nama_bank')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_rekening" class="block text-sm font-medium text-gray-700">
                            Nomor Rekening
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="no_rekening" name="no_rekening" value="{{ old('no_rekening') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 sm:text-sm p-2.5"
                            placeholder="Masukkan nomor rekening" required>
                        @error('no_rekening')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_pemilik_rekening" class="block text-sm font-medium text-gray-700">
                            Nama Pemilik Bank
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_pemilik_rekening" name="nama_pemilik_rekening"
                            value="{{ old('nama_pemilik_rekening') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 sm:text-sm p-2.5"
                            placeholder="Masukkan pemilik rekening bank" required>
                        @error('nama_pemilik_rekening')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('rekeningBank.index') }}"
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
