<header x-data="{ menuToggle: false }" class="sticky top-0 z-50 flex w-full border-gray-200 bg-white lg:border-b">
    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
        <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4">
            <button
                class="z-50 flex h-8 w-8 items-center justify-center rounded-full border bg-blue-500 text-white lg:h-9 lg:w-9 lg:border"
                @click.stop="sidebarToggle = !sidebarToggle">
                <!-- Ikon menu (desktop) -->
                <i x-show="!sidebarToggle" class="bx bx-chevrons-left"></i>

                <!-- Ikon close (mobile & desktop) -->
                <i x-show="sidebarToggle" class="bx bx-chevrons-right"></i>
            </button>

            <a href="index.html" class="lg:hidden">
                <img class="h-14 w-auto object-contain" src="{{ asset('assets/images/logo/logo-sidebar.png') }}"
                    alt="Logo" />
            </a>

            {{-- Tombol Toggle Menu Mobile --}}
            <button
                class="z-50 flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 lg:hidden"
                :class="menuToggle ? 'bg-gray-100' : ''" @click.stop="menuToggle = !menuToggle">
                <i class="fill-current bx bx-dots-vertical-rounded"></i>
            </button>
        </div>

        <!-- Desktop Menu (tetap horizontal) -->
        <div :class="menuToggle ? 'flex' : 'hidden'"
            class="w-full flex-col gap-3 px-5 py-4 shadow-lg lg:flex lg:flex-row lg:items-center lg:justify-end lg:gap-4 lg:px-0 lg:py-0 lg:shadow-none">

            <!-- Tombol POS -->
            @can('penjualan.pos')
                <a href="{{ route('pos.index') }}"
                    class="text-sm px-2 sm:px-3 py-1.5 rounded-lg bg-red-700 text-white shadow-lg hover:bg-red-800 transition-colors whitespace-nowrap text-center w-full lg:w-auto">
                    <i class="bx bx-cart"></i>
                    Kasir Penjualan (POS)
                </a>
            @endcan

            <!-- Edit Profile -->
            @can('user.edit')
                <a href="{{ route('user.edit', $currentUser->id) }}"
                    class="text-sm border border-gray-500 text-gray-500 px-2 sm:px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap text-center w-full lg:w-auto">
                    <i class="fill-gray-500 bx bx-user"></i>
                    Edit Profile
                </a>
            @endcan

            <!-- User Dropdown -->
            <div class="relative w-full lg:w-auto" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                <a class="flex items-center justify-between lg:justify-start text-gray-700 border border-gray-200 rounded-lg px-3 py-2 lg:border-0 lg:px-0 lg:py-0 hover:bg-gray-50 lg:hover:bg-transparent transition-colors"
                    href="#" @click.prevent="dropdownOpen = !dropdownOpen">
                    <div class="flex items-center">
                        <span class="mr-3 h-9 w-9 overflow-hidden rounded-full flex-shrink-0">
                            @if ($currentUser->photo_user)
                                <img src="{{ asset('storage/' . $currentUser->photo_user) }}" alt="User"
                                    class="w-full h-full object-cover" />
                            @else
                                <img src="{{ asset('assets/images/user/default-user.png') }}" alt="User"
                                    class="w-full h-full object-cover" />
                            @endif
                        </span>

                        <span class="text-theme-sm mr-1 block font-medium whitespace-nowrap truncate max-w-[150px]">
                            {{ $currentUser->name }}
                        </span>
                    </div>

                    <i :class="dropdownOpen && 'rotate-180'"
                        class="bx bx-chevron-down stroke-gray-500 transition-transform duration-200"></i>
                </a>

                <div x-show="dropdownOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="shadow-theme-lg absolute left-0 right-0 lg:left-auto lg:right-0 mt-2 flex w-full lg:w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 z-50">
                    <div>
                        <span class="text-theme-sm block font-medium text-gray-700 whitespace-nowrap truncate">
                            {{ $userRoles }}
                        </span>
                        <span class="text-theme-xs mt-0.5 block text-gray-500 whitespace-nowrap truncate">
                            {{ $currentUser->email }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="group text-theme-sm flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                            <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-log-out"></i>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
