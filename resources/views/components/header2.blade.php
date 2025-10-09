<header x-data="{ menuToggle: false, dropdownOpen: false }"
    class="sticky top-0 z-[99999] flex w-full items-center justify-between border-b border-gray-200 bg-white shadow-sm">

    <!-- Kiri: Logo -->
    <div class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3">
        <a href="{{ route('pos.index') }}" class="flex items-center gap-2">
            <!-- Logo besar untuk layar >= sm -->
            <span class="hidden sm:block">
                <img class="h-16 w-auto object-contain" src="{{ asset('assets/images/logo/logo-sidebar.png') }}"
                    alt="Logo" />
            </span>
            <!-- Logo kecil untuk layar < sm -->
            <span class="block sm:hidden">
                <img class="h-10 w-auto object-contain" src="{{ asset('assets/images/logo/logo-sidebar2.png') }}"
                    alt="Logo" />
            </span>
        </a>
    </div>


    <!-- Kanan: Navigasi & User -->
    <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3">

        <!-- Tombol Dashboard -->
        <a href="{{ route('produk.index') }}"
            class="text-xs sm:text-sm px-2 sm:px-3 py-1.5 rounded-lg bg-indigo-900 text-white shadow hover:bg-indigo-800 transition">
            <i class="bx bx-world text-base sm:text-sm"></i>
            <span class="hidden sm:inline">Kembali ke dashboard</span>
        </a>

        <!-- Profil User -->
        <div class="relative" @click.outside="dropdownOpen = false">
            <button @click.prevent="dropdownOpen = !dropdownOpen"
                class="flex items-center text-gray-700 gap-2 focus:outline-none">
                <span class="h-8 w-8 sm:h-9 sm:w-9 overflow-hidden rounded-full border border-gray-200">
                    @if (Auth::user()->photo_user)
                        <img src="{{ asset('storage/' . Auth::user()->photo_user) }}" alt="User"
                            class="h-full w-full object-cover" />
                    @else
                        <img src="{{ asset('assets/images/user/default-user.png') }}" alt="User"
                            class="h-full w-full object-cover" />
                    @endif
                </span>

                <span class="hidden sm:block text-sm font-medium truncate max-w-[100px] md:max-w-[150px]">
                    {{ Auth::user()->name }}
                </span>

                <i :class="dropdownOpen && 'rotate-180'"
                    class="bx bx-chevron-down text-gray-500 transition-transform duration-200"></i>
            </button>

            <!-- Dropdown -->
            <div x-show="dropdownOpen" x-transition
                class="absolute right-0 mt-2 w-56 sm:w-64 bg-white rounded-xl border border-gray-200 shadow-lg p-3 z-50">
                <div class="pb-2 border-b border-gray-100">
                    <span class="block text-sm font-medium text-gray-700">
                        {{ Auth::user()->getRoleNames()->implode(', ') }}
                    </span>
                    <span class="block text-xs text-gray-500 truncate">
                        {{ Auth::user()->email }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                        class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                        <i class="bx bx-log-out text-lg text-gray-500 group-hover:text-gray-700"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
