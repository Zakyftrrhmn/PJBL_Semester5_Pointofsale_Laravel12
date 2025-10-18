@extends('layouts.layout')
@section('title', 'Pengaturan Toko')
@section('subtitle', 'Atur informasi umum toko kamu di sini')

@section('content')
    {{-- ALERT SUCCESS --}}
    @if (session('success'))
        <div id="alert-1" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
            <svg class="shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('success') }}
            </div>
            <button type="button" class="ms-auto bg-green-200 text-green-600 rounded-lg p-1.5 hover:bg-green-300"
                data-dismiss-target="#alert-1" aria-label="Close">
                ✕
            </button>
        </div>
    @endif

    {{-- FORM --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6" x-data="{ showModal: false }">
        <form id="pageForm" action="{{ $page ? route('pages.update', $page->id) : route('pages.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @if ($page)
                @method('PUT')
            @endif

            {{-- ISI FORM --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Toko --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Toko <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="nama_toko" value="{{ old('nama_toko', $page->nama_toko ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('nama_toko')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Pemilik --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Pemilik <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="nama_pemilik" value="{{ old('nama_pemilik', $page->nama_pemilik ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('nama_pemilik')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- === Alamat dipecah menjadi beberapa field === --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jalan <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="jalan" value="{{ old('jalan', $page->jalan ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('jalan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelurahan <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="kelurahan" value="{{ old('kelurahan', $page->kelurahan ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('kelurahan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kecamatan <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $page->kecamatan ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('kecamatan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kota <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="kota" value="{{ old('kota', $page->kota ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('kota')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Provinsi <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="provinsi" value="{{ old('provinsi', $page->provinsi ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('provinsi')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Pos <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="kode_pos" value="{{ old('kode_pos', $page->kode_pos ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('kode_pos')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- === Kontak === --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telepon <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="telepon" value="{{ old('telepon', $page->telepon ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('telepon')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telepon 2 <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="text" name="telepon2" value="{{ old('telepon2', $page->telepon2 ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('telepon2')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email <span
                            class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                    <input type="email" name="email" value="{{ old('email', $page->email ?? '') }}"
                        class="mt-1 w-full rounded-lg border-gray-300 p-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Upload Logo --}}
            <div class="mt-8 border-t pt-6">
                <h3 class="text-sm font-semibold mb-4">Logo & Favicon</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Logo Sidebar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Sidebar <span
                                class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                        <div class="flex flex-col items-start gap-3">
                            <img id="preview_logo_sidebar"
                                src="{{ $page && $page->logo_sidebar ? asset('storage/' . $page->logo_sidebar) : asset('assets/images/logo/logo-sidebar.png') }}"
                                class="w-[180px] h-[50px] object-contain bg-gray-50 border border-gray-200 rounded-lg shadow-sm" />
                            <input type="file" name="logo_sidebar" accept="image/*"
                                onchange="previewImage(event, 'preview_logo_sidebar')"
                                class="text-sm file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>
                    </div>

                    {{-- Logo Sidebar 2 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Sidebar 2 <span
                                class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                        <div class="flex flex-col items-start gap-3">
                            <img id="preview_logo_sidebar2"
                                src="{{ $page && $page->logo_sidebar2 ? asset('storage/' . $page->logo_sidebar2) : asset('assets/images/logo/logo-sidebar2.png') }}"
                                class="w-[60px] h-[60px] object-contain bg-gray-50 border border-gray-200 rounded-lg shadow-sm" />
                            <input type="file" name="logo_sidebar2" accept="image/*"
                                onchange="previewImage(event, 'preview_logo_sidebar2')"
                                class="text-sm file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>
                    </div>

                    {{-- Logo Login --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Login <span
                                class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                        <div class="flex flex-col items-start gap-3">
                            <img id="preview_logo_login"
                                src="{{ $page && $page->logo_login ? asset('storage/' . $page->logo_login) : asset('assets/images/logo.png') }}"
                                class="w-[200px] h-[120px] object-contain bg-gray-50 border border-gray-200 rounded-lg shadow-sm" />
                            <input type="file" name="logo_login" accept="image/*"
                                onchange="previewImage(event, 'preview_logo_login')"
                                class="text-sm file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Favicon <span
                                class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full ml-1">Opsional</span></label>
                        <div class="flex flex-col items-start gap-3">
                            <img id="preview_favicon"
                                src="{{ $page && $page->favicon ? asset('storage/' . $page->favicon) : asset('assets/images/logo/favicon.png') }}"
                                class="w-[60px] h-[60px] object-contain bg-gray-50 border border-gray-200 rounded-lg shadow-sm" />
                            <input type="file" name="favicon" accept="image/*"
                                onchange="previewImage(event, 'preview_favicon')"
                                class="text-sm file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div class="mt-8 flex justify-end">
                <button type="button" @click="showModal = true"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 shadow-sm">
                    {{ $page ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>

        {{-- Modal Konfirmasi --}}
        <div x-show="showModal" class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 px-4"
            x-transition style="display: none;">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-gray-800">Konfirmasi {{ $page ? 'Perbarui' : 'Simpan' }}</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Apakah Anda yakin ingin {{ $page ? 'memperbarui' : 'menyimpan' }} data ini?
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showModal = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="button" @click="document.getElementById('pageForm').submit()"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Ya, {{ $page ? 'Perbarui' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event, id) {
            const img = document.getElementById(id);
            img.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
@endsection
