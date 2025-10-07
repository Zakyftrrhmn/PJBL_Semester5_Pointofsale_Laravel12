@extends('layouts.layout')
@section('title', 'Manajemen User')
@section('subtitle', 'Daftar semua pengguna sistem dan peran mereka')
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
            {{-- Header dan Pencarian/Tombol Tambah --}}
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">

                <div class="flex flex-col gap-2">
                    <form action="{{ route('user.index') }}" method="GET" class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <div class="relative w-64 sm:w-72">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari berdasarkan nama atau email..."
                                    class="h-10 w-full rounded-lg border border-gray-200 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                                <span class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                                    <i class="bx bx-search text-lg"></i>
                                </span>
                            </div>

                            <div class="relative group">
                                <a href="{{ route('user.index') }}"
                                    class="flex items-center justify-center h-10 w-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shadow-sm">
                                    <i class="bx bx-refresh text-xl"></i>
                                </a>
                                <span
                                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-black rounded opacity-0 group-hover:opacity-100 scale-95 group-hover:scale-100 transition-all duration-300">
                                    Reset
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                @can('user.create')
                    {{-- Tombol Aksi --}}
                    <div class="flex items-center gap-3">
                        <a href="{{ route('user.create') }}"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            <i class='bx bx-plus-circle'></i> Tambah User
                        </a>
                    </div>
                @endcan

            </div>

            {{-- Tabel User --}}
            <div class="p-5 border-t border-gray-100 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-gray-500">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                                <tr class="border-b border-gray-100">
                                    <th scope="col" class="px-5 py-3 whitespace-nowrap">#</th>
                                    <th scope="col" class="px-5 py-3 whitespace-nowrap">Nama</th>
                                    <th scope="col" class="px-5 py-3 whitespace-nowrap">Email</th>
                                    <th scope="col" class="px-5 py-3 whitespace-nowrap">Peran (Role)</th>
                                    @canany(['user.destroy', 'user.edit'])
                                        <th scope="col" class="px-5 py-3 text-center whitespace-nowrap">Aksi</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($users as $no => $user)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $users->firstItem() + $no }}</td>
                                        <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                {{-- Area Foto Profil --}}
                                                <div
                                                    class="w-10 h-10 rounded-full overflow-hidden bg-gray-100 flex-shrink-0">
                                                    @if ($user->photo_user)
                                                        <img src="{{ asset('storage/' . $user->photo_user) }}"
                                                            alt="Foto Profil" class="w-full h-full object-cover">
                                                    @else
                                                        <img src="{{ asset('assets/images/user/default-user.png') }}"
                                                            alt="Default" class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                                {{-- Nama --}}
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-black whitespace-nowrap">
                                                        {{ $user->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @forelse ($user->getRoleNames() as $role)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 whitespace-nowrap">{{ $role }}</span>
                                            @empty
                                                <span
                                                    class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 whitespace-nowrap">Tidak
                                                    Ada Peran</span>
                                            @endforelse
                                        </td>
                                        <td class="px-5 py-4 flex justify-center gap-2">
                                            {{-- Tombol Aksi --}}
                                            @can('user.edit')
                                                <a href="{{ route('user.edit', $user->id) }}"
                                                    class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200 hover:bg-gray-100">
                                                    <i class="bx bx-edit text-base"></i>
                                                </a>
                                            @endcan

                                            @can('user.destroy')
                                                @if (Auth::id() != $user->id)
                                                    @if (!$user->hasRole('Super Admin'))
                                                        <button
                                                            @click="showModal = true; deleteUrl = '{{ route('user.destroy', $user->id) }}'"
                                                            class="p-2 border rounded-lg shadow-sm text-gray-700 border-gray-200 hover:bg-gray-100">
                                                            <i class="bx bx-trash text-base"></i>
                                                        </button>
                                                    @else
                                                        <span class="p-2 text-red-500 cursor-not-allowed"
                                                            title="Super Admin cannot be deleted">
                                                            <i class='bx bx-lock-alt text-base'></i>
                                                        </span>
                                                    @endif
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-6 text-center text-gray-400 text-sm">
                                            Tidak ada data user.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $users->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>


    </div>

@endsection
