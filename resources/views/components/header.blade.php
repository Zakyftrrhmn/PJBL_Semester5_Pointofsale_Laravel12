        <header x-data="{ menuToggle: false }" class="sticky top-0 z-99999 flex w-full border-gray-200 bg-white lg:border-b">
            <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
                <div
                    class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4">
                    <button
                        class="z-99999 flex h-8 w-8 items-center justify-center rounded-full border bg-blue-500 text-white lg:h-9 lg:w-9 lg:border"
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
                    <button
                        class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 lg:hidden"
                        :class="menuToggle ? 'bg-gray-100' : ''" @click.stop="menuToggle = !menuToggle">
                        <i class="fill-current bx bx-dots-vertical-rounded"></i>
                    </button>
                </div>

                <div :class="menuToggle ? 'flex' : 'hidden'"
                    class="shadow-theme-md w-full items-center justify-between gap-4 px-5 py-4 lg:flex lg:justify-end lg:px-0 lg:shadow-none">
                    <div class="2xsm:gap-3 flex items-center gap-2">
                        <div class="relative" x-data="{ dropdownOpen: false, notifying: {{ $stokRendahProduks->count() > 0 ? 'true' : 'false' }} }" @click.outside="dropdownOpen = false">
                            <button
                                class="hover:text-dark-900 relative flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                @click.prevent="dropdownOpen = ! dropdownOpen; notifying = false">
                                <span :class="!notifying ? 'hidden' : 'flex'"
                                    class="absolute top-0.5 right-0 z-1 h-2 w-2 rounded-full bg-orange-400">
                                    <span
                                        class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
                                </span>
                                <i class="fill-current bx bxs-bell"></i>
                                {{-- Tampilkan jumlah notifikasi jika ada --}}
                                @if ($stokRendahProduks->count() > 0)
                                    <span
                                        class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                        {{ $stokRendahProduks->count() }}
                                    </span>
                                @endif
                            </button>

                            <div x-show="dropdownOpen"
                                class="shadow-theme-lg absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 sm:w-[361px] lg:right-0">
                                <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3">
                                    <h5 class="text-lg font-semibold text-gray-800">
                                        Notifikasi Stok Rendah ({{ $stokRendahProduks->count() }})
                                    </h5>

                                    <button @click="dropdownOpen = false" class="text-gray-500">
                                        <i class="fill-current bx bx-x"></i>
                                    </button>
                                </div>

                                <ul class="custom-scrollbar flex h-auto flex-col overflow-y-auto">
                                    {{-- Loop Produk Stok Rendah --}}
                                    @forelse ($stokRendahProduks as $produk)
                                        <li>
                                            {{-- Anda dapat mengganti '#' dengan rute ke halaman detail/edit produk --}}
                                            <a class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100"
                                                href="#">

                                                {{-- Photo Produk --}}
                                                <span
                                                    class="relative z-1 block h-10 w-full max-w-10 rounded-full overflow-hidden">

                                                    <img src="{{ $produk->photo_produk ? asset('storage/' . $produk->photo_produk) : asset('assets/images/produk/default-produk.png') }}"
                                                        alt="{{ $produk->nama_produk }}"
                                                        class="object-cover w-full h-full" />
                                                </span>

                                                <span class="block">
                                                    <span class="text-theme-sm mb-1.5 block text-gray-500">
                                                        <span
                                                            class="font-medium text-gray-800">{{ $produk->nama_produk }}
                                                            ({{ $produk->kode_produk }})
                                                        </span>
                                                        stok tersisa:
                                                        <span
                                                            class="font-medium text-red-600">{{ $produk->stok_produk }}</span>
                                                        unit, batas pengingat: {{ $produk->pengingat_stok }} unit.
                                                    </span>

                                                    <span class="text-theme-xs flex items-center gap-2 text-gray-500">
                                                        <span>Stok Rendah</span>
                                                        <span class="h-1 w-1 rounded-full bg-gray-400"></span>
                                                        <span>{{ $produk->updated_at->diffForHumans() }}</span>
                                                    </span>
                                                </span>
                                            </a>
                                        </li>
                                    @empty
                                        <li>
                                            <p class="p-3 text-center text-gray-500">Tidak ada notifikasi stok rendah
                                                saat ini. ✨</p>
                                        </li>
                                    @endforelse
                                </ul>

                                @if ($stokRendahProduks->count() > 0)
                                    <a href="#"
                                        class="text-theme-sm shadow-theme-xs mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800">
                                        Lihat Semua Produk Stok Rendah
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>


                    <a href="" class="text-sm px-3 py-1 rounded-lg bg-indigo-900 text-white shadow-lg">
                        <i class="bx bx-cart"></i>
                        Kasir (POS)
                    </a>


                    <button
                        class="text-sm border border-gray-500 text-gray-500 px-3 py-1 rounded-lg hover:bg-blue-200 hover:text-blue-700 hover:border-none transition-colors">
                        <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-user"></i>
                        Edit Profile
                    </button>




                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <a class="flex items-center text-gray-700" href="#"
                            @click.prevent="dropdownOpen = ! dropdownOpen">
                            <span class="mr-3 h-9 w-9 overflow-hidden rounded-full">
                                <img src="{{ asset('assets/images/user/default-pelanggan.png') }}" alt="User" />
                            </span>

                            <span class="text-theme-sm mr-1 block font-medium"> Musharof </span>

                            <i :class="dropdownOpen && 'rotate-180'" class="bx bx-chevron-down stroke-gray-500"></i>
                        </a>

                        <div x-show="dropdownOpen"
                            class="shadow-theme-lg absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3">
                            <div>
                                <span class="text-theme-sm block font-medium text-gray-700">
                                    Musharof Chowdhury
                                </span>
                                <span class="text-theme-xs mt-0.5 block text-gray-500">
                                    randomuser@pimjo.com
                                </span>
                            </div>

                            {{-- <ul class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3">
                                <li>
                                    <a href="profile.html"
                                        class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                        <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-user"></i>
                                        Edit profile
                                    </a>
                                </li>
                            </ul> --}}
                            <button
                                class="group text-theme-sm mt-3 flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-log-out"></i>

                                Sign out
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </header>
