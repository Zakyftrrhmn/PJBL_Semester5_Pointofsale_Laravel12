@extends('layouts.layout')
@section('title', 'Edit Pemasok')
@section('subtitle', 'Perbarui detail pemasok di bawah ini')
@section('content')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <form action="{{ route('pemasok.update', $pemasok->id) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nama_pemasok" class="block text-sm font-medium text-gray-700">Nama Pemasok <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nama_pemasok" name="nama_pemasok"
                            value="{{ old('nama_pemasok', $pemasok->nama_pemasok) }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            required>
                        @error('nama_pemasok')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telp" class="block text-sm font-medium text-gray-700">Nomor Telepon <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="telp" name="telp" value="{{ old('telp', $pemasok->telp) }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            required>
                        @error('telp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $pemasok->email) }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700">
                            Alamat
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="alamat" name="alamat" value="{{ old('alamat', $pemasok->alamat) }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                        @error('alamat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="photo_pemasok" class=" mb-2 text-sm font-medium text-gray-700 flex gap-x-3">
                            Foto
                            <span class="text-xs text-green-800 bg-green-200 px-2 py-1 rounded-full shadow-sm">
                                Opsional & Rekomandasi Photo
                                ukuran 1:1</span>
                        </label>
                        <input type="file" id="photo_pemasok" name="photo_pemasok"
                            class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-lg shadow-sm cursor-pointer focus:border-blue-400 focus:ring focus:ring-blue-100">
                        @if ($pemasok->photo_pemasok)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $pemasok->photo_pemasok) }}" alt="Foto"
                                    class="w-16 h-16 rounded-full object-cover">
                            </div>
                        @endif
                        @error('photo_pemasok')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('pemasok.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</a>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
