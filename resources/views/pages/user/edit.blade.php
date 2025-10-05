@extends('layouts.layout')
@section('title', 'Edit User: ' . $user->name)
@section('subtitle', 'Perbarui detail pengguna, termasuk nama, email, password, peran, dan foto profil.')
@section('content')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                {{-- Gunakan method POST dengan @method('PUT') untuk update, dan tambahkan enctype untuk file upload --}}
                <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Upload Foto User --}}
                    <div class="flex items-start gap-6">
                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border border-gray-300 bg-gray-50 flex items-center justify-center">
                            {{-- Tampilkan foto user saat ini, jika ada. Gunakan placeholder jika tidak ada. --}}
                            @php
                                $photoPath = $user->photo_user
                                    ? asset('storage/' . $user->photo_user)
                                    : asset('assets/images/user/default-user.png');
                            @endphp
                            <img id="preview-image" src="{{ $photoPath }}" alt="Preview User"
                                class="w-full h-full object-cover">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto User (Ganti)</label>
                            <input type="file" id="photo_user" name="photo_user" accept="image/*"
                                class="mt-2 block w-full text-sm text-gray-700
                                       file:mr-4 file:rounded-lg file:border-0
                                       file:bg-blue-600 file:px-4 file:py-2
                                       file:text-sm file:font-medium file:text-white
                                       hover:file:bg-blue-700" />
                            <p class="text-xs text-gray-500 mt-1">Upload foto baru untuk mengganti foto profil saat ini (Max
                                2MB, format JPG/PNG). Opsional.</p>
                            @error('photo_user')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Form Input Data Diri dan Akun (2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Masukkan nama lengkap" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Masukkan alamat email" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password (Opsional) --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <input type="password" id="password" name="password"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Kosongkan jika tidak ingin mengubah password">
                            <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter. Kosongkan jika tidak diubah.</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi
                                Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="mt-1 block w-full rounded-lg border-gray-300 p-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                placeholder="Ulangi password baru">
                        </div>
                    </div>

                    {{-- Pilih Peran (Role) --}}
                    <div class="space-y-1">
                        <label for="roles" class="block text-sm font-semibold text-gray-800">
                            Pilih Peran (Role) <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            @php
                                // Mendapatkan peran yang sudah dimiliki user
                                $currentRoles = $user->getRoleNames()->toArray();
                                // Menentukan peran yang harus dipilih (utamakan old, jika tidak ada gunakan current)
                                $selectedRoles = old('roles', $currentRoles);
                            @endphp

                            <select name="roles[]" id="roles" multiple
                                class="peer mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm transition-all
                   focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none hover:border-gray-300"
                                required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}"
                                        {{ in_array($role, $selectedRoles) ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Icon kecil di kanan atas (tanda panah) --}}
                            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-400 peer-focus:text-blue-400"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                        <div class="flex items-start gap-2 mt-1">
                            {{-- Icon Info --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-[2px] text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 18.5A6.5 6.5 0 105.5 12a6.5 6.5 0 006.5 6.5z" />
                            </svg>
                            <p class="text-xs text-gray-500">
                                Tekan
                                <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-300 rounded text-[10px]">Ctrl</kbd>
                                (Windows) atau
                                <kbd
                                    class="px-1 py-0.5 bg-gray-100 border border-gray-300 rounded text-[10px]">Command</kbd>
                                (Mac) untuk memilih lebih dari satu peran.
                            </p>
                        </div>

                        @error('roles')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('user.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Batal</a>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Perbarui
                            User</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Script untuk Preview Gambar --}}
    <script>
        document.getElementById('photo_user').addEventListener('change', function(e) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').setAttribute('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        });
    </script>

@endsection
