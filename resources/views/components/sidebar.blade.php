<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="sidebar-header flex items-center gap-2 pb-7 pt-8">
        <a href="index.html">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="" src="{{ asset('assets/images/logo/logo.svg') }}" alt="Logo" />
                <img class="hidden" src="{{ asset('assets/images/logo/logo-dark.svg') }}" alt="Logo" />
            </span>

            <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="{{ asset('assets/images/logo/logo-icon.svg') }}" alt="Logo" />
        </a>
    </div>
    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav x-data="{ selected: $persist('Dashboard') }">
            <div>
                <hr class="w-full mb-2 opacity-70">

                <!-- MAIN -->
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Main
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col">
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bxs-dashboard"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                        </a>
                    </li>
                </ul>

                <!-- INVENTORY -->
                <hr class="w-full mb-2 opacity-70">

                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Inventory
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col">
                    <li>
                        <a href=""
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/product*') ? 'menu-item-active' : 'text-gray-500' }}">
                            <i class="bx bxs-box"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kategori.index') }}"
                            class="menu-item group hover:menu-item-active {{ request()->is('admin/kategori*') ? 'menu-item-active' : 'text-gray-500' }}">
                            <i class="bx bxs-category"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Kategori</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pelanggan.index') }}"
                            class="menu-item group hover:menu-item-active  {{ request()->is('admin/pelanggan*') ? 'menu-item-active' : 'text-gray-500' }}">
                            <i class="bx bx-group"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pelanggan</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-package"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Stock
                                Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-transfer"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Suppliers</span>
                        </a>
                    </li>
                </ul>

                <!-- SALES -->
                <hr class="w-full mb-2 opacity-70">
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Sales
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col">
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-cart"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">POS
                                (Transaction)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-receipt"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Sales History</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-bar-chart"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Reports</span>
                        </a>
                    </li>
                </ul>

                <!-- USER MANAGEMENT -->
                <hr class="w-full mb-2 opacity-70">
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        User Management
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col">
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-user"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-shield"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Roles &
                                Permissions</span>
                        </a>
                    </li>
                </ul>

                <!-- PEOPLES -->
                <hr class="w-full mb-2 opacity-70">
                <h3 class="mb-2 text-xs  text-indigo-900 flex items-center justify-between text-lg font-medium">
                    <span class="menu-group-title" class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Peoples
                    </span>
                    <i :class="sidebarToggle ? 'opacity-100 visible lg:block ' : 'opacity-0 invisible'"
                        class="transition duration-300 menu-group-icon mx-auto bx bx-dots-horizontal-rounded !text-center"></i>
                </h3>
                <ul class="mb-6 flex flex-col">

                    <li>
                        <a href="#" class="menu-item group text-gray-500 hover:menu-item-active">
                            <i class="bx bx-user-check"></i>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Cashiers</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
