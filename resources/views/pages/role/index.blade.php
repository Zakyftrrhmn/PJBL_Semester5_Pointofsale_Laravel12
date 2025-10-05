@extends('layouts.layout')
@section('title', 'Role & Permissions')
@section('subtitle', 'Kelola peran pengguna dan hak akses')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Roles</h2>
                    @can('role.create')
                        <a href="{{ route('role.create') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">
                            <i class='bx bx-plus text-xl mr-1'></i>
                            Tambah Role
                        </a>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Role</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hak Akses</th>
                                @canany(['role.edit', 'role.destroy'])
                                    <th
                                        class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($roles as $role)
                                <tr>
                                    <td class="px-5 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $role->name }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="text-xs text-indigo-800 bg-indigo-100 px-2 py-1 rounded-full shadow-sm">
                                            {{ $role->permissions->count() }} Permissions
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @can('role.edit')
                                                <a href="{{ route('role.edit', $role->id) }}"
                                                    class="inline-flex items-center justify-center rounded-lg p-2 border text-xs shadow-sm text-gray-700 border-gray-200">
                                                    <i class='bx bx-edit text-base'></i>
                                                </a>
                                            @endcan

                                            @can('role.destroy')
                                                <button
                                                    @click="showModal = true; deleteUrl = '{{ route('role.destroy', $role->id) }}'"
                                                    class="inline-flex items-center justify-center rounded-lg p-2 border text-xs shadow-sm text-gray-700 border-gray-200">
                                                    <i class='bx bx-trash text-base'></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-gray-400 text-sm">
                                        Tidak ada data role.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $roles->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>
    </div>

@endsection
