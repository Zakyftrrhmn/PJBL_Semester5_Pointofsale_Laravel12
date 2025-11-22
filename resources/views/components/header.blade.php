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
                                        Notifikasi ({{ $stokRendahProduks->count() }})
                                    </h5>

                                    <button @click="dropdownOpen = false" class="text-gray-500">
                                        <i class="fill-current bx bx-x"></i>
                                    </button>
                                </div>

                                <ul class="custom-scrollbar flex h-auto flex-col overflow-y-auto">
                                    @forelse ($stokRendahProduks as $produk)
                                        <li>
                                            <a href="{{ route('produk.show', $produk->id) }}"
                                                class="flex gap-3 py-3 px-4 border-b border-gray-100 rounded-lg hover:bg-gray-50 transition">

                                                {{-- FOTO --}}
                                                <div class="w-11 h-11 rounded-md overflow-hidden bg-gray-200">
                                                    <img src="{{ $produk->photo_produk
                                                        ? asset('storage/' . $produk->photo_produk)
                                                        : asset('assets/images/produk/default-produk.png') }}"
                                                        class="object-cover w-full h-full"
                                                        alt="{{ $produk->nama_produk }}">
                                                </div>

                                                {{-- INFO PRODUK --}}
                                                <div class="flex flex-col flex-1">

                                                    {{-- NAMA --}}
                                                    <span class="font-semibold text-gray-800 text-sm leading-tight">
                                                        {{ $produk->nama_produk }} ({{ $produk->kode_produk }})
                                                    </span>

                                                    {{-- STATUS & TANGGAL --}}
                                                    <div class="flex items-center gap-2 text-[11px]">

                                                        @if ($produk->status === 'urgent')
                                                            <span
                                                                class="text-red-600 font-bold bg-red-100 px-1.5 py-0.5 rounded text-[10px]">
                                                                ⚠ Urgent
                                                            </span>
                                                        @elseif ($produk->status === 'warning')
                                                            <span
                                                                class="text-yellow-700 font-semibold bg-yellow-100 px-1.5 py-0.5 rounded text-[10px]">
                                                                ⚠ Warning
                                                            </span>
                                                        @elseif ($produk->status === 'slow')
                                                            <span
                                                                class="text-blue-700 font-semibold bg-blue-100 px-1.5 py-0.5 rounded text-[10px]">
                                                                💤 Slow Moving
                                                            </span>
                                                        @endif

                                                        <span class="text-gray-400">•</span>
                                                        <span
                                                            class="text-gray-500">{{ $produk->updated_at->diffForHumans() }}</span>
                                                    </div>


                                                    {{-- INFO STATISTIK --}}
                                                    <div class="mt-1 text-[11.5px]">

                                                        {{-- Stok & Estimasi --}}
                                                        <div class="text-gray-600">
                                                            Stok: <strong
                                                                class="text-red-600">{{ $produk->stok_produk }}</strong>
                                                            unit
                                                            @if ($produk->habis_hari <= 1)
                                                                (habis <strong>hari ini</strong>)
                                                            @else
                                                                (± <strong>{{ $produk->habis_hari }} hari</strong>
                                                                lagi)
                                                            @endif
                                                        </div>

                                                        {{-- Rekomendasi AI --}}
                                                        @if ($produk->rekomendasi_beli > 0)
                                                            <div
                                                                class="mt-1 p-1.5 rounded-md bg-gray-100 border border-gray-200">
                                                                <span class="text-[10.5px] text-gray-700">
                                                                    💡 Saran restock:
                                                                    <strong
                                                                        class="text-indigo-700">+{{ $produk->rekomendasi_beli }}
                                                                        unit</strong><br>
                                                                    <span class="text-gray-500">
                                                                        Penjualan rata-rata:
                                                                        <strong>{{ $produk->avg_penjualan }}/hari</strong>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        @endif

                                                        {{-- Jika slow moving --}}
                                                        @if ($produk->status === 'slow')
                                                            <div
                                                                class="mt-1 p-2 bg-blue-50 border border-blue-200 rounded-md text-[11px]">
                                                                <div class="text-blue-800 font-semibold">
                                                                    Produk tidak laku ({{ $produk->days_no_sale }}
                                                                    hari)
                                                                </div>
                                                                <div class="text-blue-700">
                                                                    💡 Saran: lakukan diskon, bundling, atau promo untuk
                                                                    mempercepat perputaran stok.
                                                                </div>
                                                            </div>
                                                        @endif


                                                    </div>

                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="text-center text-gray-500 text-[12px] py-4">
                                            Tidak ada notifikasi saat ini ✨
                                        </li>
                                    @endforelse


                                </ul>

                                @if ($stokRendahProduks->count() > 0)
                                    <a href="#"
                                        class="text-theme-sm shadow-theme-xs mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800">
                                        Lihat Semua Produk
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @can('penjualan.pos')
                        <a href="{{ route('pos.index') }}"
                            class="text-sm px-3 py-1 rounded-lg bg-red-700 text-white shadow-lg whitespace-nowrap">
                            <i class="bx bx-cart"></i>
                            Kasir Transaksi (POS)
                        </a>
                    @endcan

                    @auth
                        <a href="{{ route('user.edit', Auth::user()->id) }}"
                            class="text-sm border border-gray-500 text-gray-500 px-3 py-1 rounded-lg    transition-colors whitespace-nowrap">
                            <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-user "></i>
                            Edit Profile
                        </a>
                    @endauth
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <a class="flex items-center text-gray-700" href="#"
                            @click.prevent="dropdownOpen = ! dropdownOpen">
                            <span class="mr-3 h-9 w-9 overflow-hidden rounded-full">
                                @if (Auth::user()->photo_user)
                                    <img src="{{ asset('storage/' . Auth::user()->photo_user) }}" alt="User" />
                                @else
                                    <img src="{{ asset('assets/images/user/default-user.png') }}" alt="User" />
                                @endif
                            </span>

                            <span class="text-theme-sm mr-1 block font-medium whitespace-nowrap">
                                {{ Auth::user()->name }} </span>

                            <i :class="dropdownOpen && 'rotate-180'" class="bx bx-chevron-down stroke-gray-500"></i>
                        </a>

                        <div x-show="dropdownOpen"
                            class="shadow-theme-lg absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3">
                            <div>
                                <span class="text-theme-sm block font-medium text-gray-700 whitespace-nowrap">
                                    {{ Auth::user()->getRoleNames()->implode(', ') }}
                                </span>
                                <span class="text-theme-xs mt-0.5 block text-gray-500 whitespace-nowrap">
                                    {{ Auth::user()->email }}
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
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="group text-theme-sm flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                    <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-log-out"></i>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>
