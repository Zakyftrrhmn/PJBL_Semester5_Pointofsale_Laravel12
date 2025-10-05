@extends('layouts.layout')
@section('title', 'Edit Role: ' . $role->name)
@section('subtitle', 'Perbarui nama dan hak akses role')
@section('content')

    <div class="space-y-6">
        {{-- Bagian Edit Nama Role --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Perbarui Nama Role</h3>
                <form action="{{ route('role.update', $role->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Role <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 p-2.5 sm:text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">Perbarui
                            Nama</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bagian Atur Hak Akses (Permissions) --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Atur Hak Akses (Permissions)</h3>
                <form action="{{ route('role.update.permissions', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        @foreach ($permissions as $group => $permissionList)
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <h4 class="text-base font-semibold text-indigo-700 mb-3">{{ $group }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach ($permissionList as $permission)
                                        <div class="flex items-start">
                                            <input id="permission-{{ $permission->name }}" type="checkbox"
                                                name="permissions[]" value="{{ $permission->name }}"
                                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="permission-{{ $permission->name }}"
                                                class="ms-2 text-sm font-medium text-gray-900">
                                                {{ $permission->description }}
                                                <span class="text-xs text-gray-500 block">({{ $permission->name }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:ring focus:ring-green-200">Simpan
                            Hak Akses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
