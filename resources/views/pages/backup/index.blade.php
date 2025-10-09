@extends('layouts.layout')
@section('title', 'Backup Data')
@section('subtitle', 'Kelola cadangan database dan file sistem Anda')
@section('content')

    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 mb-4 text-green-800 rounded-lg bg-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-red-800 rounded-lg bg-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Aksi Backup</h2>

            <p class="text-gray-600 mb-4">
                Tekan tombol di bawah untuk membuat cadangan (backup) database dan file-file penting (seperti foto produk,
                user, dll.) **sekarang juga**.
            </p>
            @can('backup.create')
                <form action="{{ route('backup.import') }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin mengimpor data terbaru sekarang?');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        <i class="bx bx-import text-lg mr-2"></i>
                        Import Data Sekarang
                    </button>
                </form>
            @endcan

        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat File Backup (Local Storage)</h2>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama File
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ukuran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Dibuat
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($backups as $backup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $backup['filename'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('backup.download', $backup['filename']) }}"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700">
                                                <i class="bx bx-download mr-1"></i> Download
                                            </a>
                                            @can('backup.delete')
                                                <!-- Delete -->
                                                <button
                                                    @click="showModal = true; deleteUrl = '{{ route('backup.delete', $backup['filename']) }}'"
                                                    class="inline-flex items-center justify-center rounded-lg p-2 border text-xs shadow-sm text-gray-700 border-gray-200">
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Belum ada file backup yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
                <p class="mt-4 text-xs text-gray-500">
                    *File backup tersimpan di lokasi server Anda (Disk:
                    `{{ config('backup.backup.destination.disks')[0] ?? 'local' }}`).
                </p>
            </div>
        </div>
    </div>
@endsection
