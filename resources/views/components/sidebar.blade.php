<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <div :class="sidebarToggle ? 'justify-center' : 'justify-center'"
        class="sidebar-header flex items-center gap-2 pb-3 pt-3">

        <a href="index.html">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="h-18 w-auto object-contain"
                    src="{{ $page && $page->logo_sidebar ? asset('storage/' . $page->logo_sidebar) : asset('assets/images/logo/logo-sidebar.png') }}"
                    alt="Logo" />
            </span>

            <img class="logo-icon h-18 w-auto object-contain" :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="{{ $page && $page->logo_sidebar2 ? asset('storage/' . $page->logo_sidebar2) : asset('assets/images/logo/logo-sidebar2.png') }}"
                alt="Logo" />
        </a>
    </div>
    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav x-data="{ selected: $persist('Dashboard') }">
            <div>
                @canany(['dashboard.index', 'pages.index', 'rekeningBank.index '])
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Main</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>

                    <ul class="mb-6 flex flex-col gap-y-0.5">
                        @can('dashboard.index')
                            <li>
                                <a href="{{ route('dashboard.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/dashboard*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bxs-dashboard text-xl"></i>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Dropdown content -->
                        @canany(['pages.index', 'rekeningBank.index'])
                            <!-- Dropdown Menu Start -->
                            <li x-data="{ open: {{ request()->is('admin/pages*') || request()->is('admin/rekeningBank*') ? 'true' : 'false' }} }">
                                <a href="#" @click.prevent="open = !open"
                                    class="menu-item group flex items-center justify-between"
                                    :class="open ? 'menu-item-active' : 'menu-item-inactive'">
                                    <div class="flex items-center gap-2">
                                        <i class="bx bx-menu text-xl"></i>
                                        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Informasi Toko
                                        </span>
                                    </div>
                                    <i class="bx bx-chevron-down text-xl transition-transform duration-300"
                                        :class="open ? 'rotate-180' : ''"></i>
                                </a>

                                <ul x-show="open" x-transition class="menu-dropdown mt-2 flex flex-col gap-1">
                                    @can('pages.index')
                                        <li>
                                            <a href="{{ route('pages.index') }}"
                                                class="menu-dropdown-item group {{ request()->is('admin/pages*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                                <i class="bx bx-file text-base mr-2"></i>
                                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Profil
                                                    Toko</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('rekeningBank.index')
                                        <li>
                                            <a href="{{ route('rekeningBank.index') }}"
                                                class="menu-dropdown-item group {{ request()->is('admin/rekeningBank*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                                <i class="bx bx-credit-card text-base mr-2"></i>
                                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Daftar Nomor
                                                    Rekening</span>
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        <!-- Dropdown Menu End -->

                    </ul>
                @endcanany


                @canany(['produk.index', 'kategori.index'])
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Inventory</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-y-0.5">

                        @can('produk.index')
                            <li>
                                <a href="{{ route('produk.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/produk*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-basket text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Produk</span>
                                </a>
                            </li>
                        @endcan
                        {{-- 
                        @can('barcode.index')
                            <li>
                                <a href="{{ route('barcode.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/barcode*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-barcode text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Print
                                        Barcode</span>
                                </a>
                            </li>
                        @endcan --}}

                        @can('kategori.index')
                            <li>
                                <a href="{{ route('kategori.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/kategori*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-list-ul text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Kategori</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endcanany

                @canany(['invoice.index'])
                    {{-- 'retur-penjualan.index' --}}
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Penjualan</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-y-0.5">

                        @can('invoice.index')
                            <li>
                                <a href="{{ route('invoice.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/invoice*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class='bx bx-receipt text-xl'></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Riwayat
                                        Penjualan</span>
                                </a>
                            </li>
                        @endcan

                        {{-- @can('retur-penjualan.index')
                            <li>
                                <a href="{{ route('retur-penjualan.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/retur-penjualan*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class='bx bx-undo text-xl'></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Retur
                                        Penjualan</span>
                                </a>
                            </li>
                        @endcan --}}
                    </ul>
                @endcanany

                @canany(['pelanggan.index', 'pemasok.index'])
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Peoples</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-y-0.5">
                        @can('pelanggan.index')
                            <li>
                                <a href="{{ route('pelanggan.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/pelanggan*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-group text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pelanggan</span>
                                </a>
                            </li>
                        @endcan

                        @can('pemasok.index')
                            <li>
                                <a href="{{ route('pemasok.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/pemasok*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class='bx bxs-business text-xl'></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pemasok</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endcanany

                @canany(['pembelian.create', 'pesanan-pembelian.index', 'retur-pembelian.index'])
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Pembelian</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-y-0.5">
                        @can('pembelian.create')
                            <li>
                                <a href="{{ route('pembelian.create') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/pembelian/create') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-cart text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pembelian</span>
                                </a>
                            </li>
                        @endcan

                        @can('pesanan-pembelian.index')
                            <li>
                                <a href="{{ route('pesanan-pembelian.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/pesanan-pembelian*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-receipt text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pesanan
                                        Pembelian</span>
                                </a>
                            </li>
                        @endcan

                        @can('retur-pembelian.index')
                            <li>
                                <a href="{{ route('retur-pembelian.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/retur-pembelian*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-undo text-xl"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Retur
                                        Pembelian</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endcanany


                @can('laporan.index')
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">

                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">Laporan</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>

                    <ul class="mb-6 flex flex-col gap-y-0.5">
                        <li>
                            <a href="{{ route('laporan.pembelian.index') }}"
                                class="menu-item group hover:menu-item-active {{ request()->is('admin/laporan/pembelian*') ? 'menu-item-active' : 'text-gray-800' }}">
                                <i class="bx bx-file text-xl"></i>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Laporan
                                    Pembelian</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('laporan.penjualan.index') }}"
                                class="menu-item group hover:menu-item-active {{ request()->is('admin/laporan/penjualan*') ? 'menu-item-active' : 'text-gray-800' }}">
                                <i class="bx bx-receipt text-xl"></i>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Laporan
                                    Penjualan</span>
                            </a>
                        </li>
                    </ul>
                @endcan

                @canany(['user.index', 'role.index', 'database.backup'])
                    <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                    <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">User Management</span>
                        <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                            class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-y-0.5">
                        @can('user.index')
                            <li>
                                <a href="{{ route('user.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/user*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-user"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
                                </a>
                            </li>
                        @endcan

                        @can('role.index')
                            <li>
                                <a href="{{ route('role.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/role*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-shield"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Roles &
                                        Permissions</span>
                                </a>
                            </li>
                        @endcan

                        @can('backup.index')
                            {{-- <li>
                                <a href="{{ route('backup.index') }}"
                                    class="menu-item group hover:menu-item-active {{ request()->is('admin/backup*') ? 'menu-item-active' : 'text-gray-800' }}">
                                    <i class="bx bx-hdd"></i>
                                    <span : class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Backup
                                        Data</span>
                                </a>
                            </li> --}}
                        @endcan
                    </ul>
                @endcanany

            </div>
        </nav>
    </div>
</aside>
