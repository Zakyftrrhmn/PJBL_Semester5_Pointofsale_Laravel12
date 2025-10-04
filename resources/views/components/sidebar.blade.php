<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <div :class="sidebarToggle ? 'justify-center' : 'justify-center'"
        class="sidebar-header flex items-center gap-2 pb-3 pt-3">

        <a href="index.html">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="h-18 w-auto object-contain" src="{{ asset('assets/images/logo/logo-sidebar.png') }}"
                    alt="Logo" />
            </span>

            <img class="logo-icon h-18 w-auto object-contain" :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="{{ asset('assets/images/logo/logo-sidebar2.png') }}" alt="Logo" />
        </a>
    </div>
    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav x-data="{ selected: $persist('Dashboard') }">
            <div>
                <hr class="w-full mb-2 bg-indigo-900 opacity-70">

                <!-- MAIN -->
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Main
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col gap-y-0.5">
                    <li>
                        <a href="#" class="menu-item group text-gray-800 hover:menu-item-active">
                            <i class="bx bxs-dashboard"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                        </a>
                    </li>
                </ul>

                <!-- INVENTORY -->
                <hr class="w-full mb-2 bg-indigo-900 opacity-70">

                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Inventory
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col gap-y-0.5">

                    <li>
                        <a href="{{ route('produk.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/produk*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-basket text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Produk</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('kategori.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/kategori*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-list-ul text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Kategori</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('merek.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/merek*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-badge-check text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Merek</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('satuan.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/satuan*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-box text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Satuan</span>
                        </a>
                    </li>
                </ul>


                <!-- USER MANAGEMENT -->
                <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        User Management
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col gap-y-0.5">
                    <li>
                        <a href="#" class="menu-item group text-gray-800 hover:menu-item-active">
                            <i class="bx bx-user"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-800 hover:menu-item-active">
                            <i class="bx bx-shield"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Roles &
                                Permissions</span>
                        </a>
                    </li>
                </ul>


                <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                <h3 class="mb-2 text-xs text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Pembelian
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col gap-y-0.5">
                    {{-- Menu Pembelian Baru --}}
                    <li>
                        <a href="{{ route('pembelian.create') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/pembelian/create') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-cart text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pembelian</span>
                        </a>
                    </li>
                    {{-- Menu Pesanan Pembelian --}}
                    <li>
                        <a href="{{ route('pesanan-pembelian.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/pesanan-pembelian*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-receipt text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pesanan
                                Pembelian</span>
                        </a>
                    </li>
                    {{-- Menu Retur Pembelian --}}
                    <li>
                        <a href="{{ route('retur-pembelian.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/retur-pembelian*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-undo text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Retur
                                Pembelian</span>
                        </a>
                    </li>
                </ul>



                <!-- PEOPLES -->
                <hr class="w-full mb-2 bg-indigo-900 opacity-70">
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Peoples
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col gap-y-0.5">
                    <li>
                        <a href="{{ route('pelanggan.index') }}"
                            class="menu-item group hover:menu-item-active  {{ request()->is('admin/pelanggan*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">
                            <i class="bx bx-group text-xl flex-shrink-0 text-center"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pelanggan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pemasok.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/pemasok*') ? 'menu-item-active' : 'text-gray-800' }} flex items-center gap-2">

                            <i class='bx bxs-business text-xl flex-shrink-0 text-center'></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pemasok</span>
                        </a>
                    </li>


                </ul>
            </div>
        </nav>
    </div>
</aside>
