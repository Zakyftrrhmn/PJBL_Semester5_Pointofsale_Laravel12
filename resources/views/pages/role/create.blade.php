@extends('layouts.layout')
@section('title', 'Tambah Role')
@section('subtitle', 'Isi nama role baru')
@section('content')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <form action="{{ route('role.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Role <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            placeholder="Contoh: Kasir, Staf Gudang" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('role.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</a>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
