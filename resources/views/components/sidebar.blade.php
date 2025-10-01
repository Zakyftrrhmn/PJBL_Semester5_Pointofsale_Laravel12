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
                    <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            MENU
                        </span>

                        <i :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="menu-group-icon mx-auto bx bx-dots-horizontal-rounded"></i>
                    </h3>

                    <ul class="mb-6 flex flex-col">

                        <li>
                            <a href="tables.html" class="menu-item group text-gray-500 hover:menu-item-active">
                                <i class="menu-item-icon-active bx bxs-grid-alt"></i>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Product
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="tables.html" class="menu-item group text-gray-500 hover:menu-item-active">
                                <i class=" bx bxs-grid-alt"></i>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Category
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="tables.html" class="menu-item group text-gray-500 hover:menu-item-active">
                                <i class=" bx bxs-grid-alt"></i>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Category
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="tables.html" class="menu-item group text-gray-500 hover:menu-item-active">
                                <i class=" bx bxs-grid-alt"></i>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Category
                                </span>
                            </a>
                        </li>
                        {{-- <li>
                  <a
                    href="#"
                    @click.prevent="selected = (selected === 'Pages' ? '':'Pages')"
                    class="menu-item group"
                    :class="(selected === 'Pages') || (page === 'fileManager' || page === 'pricingTables' || page === 'blank' || page === 'page404' || page === 'page500' || page === 'page503' || page === 'success' || page === 'faq' || page === 'comingSoon' || page === 'maintenance') ? 'menu-item-active' : 'menu-item-inactive'"
                  >
                    <i
                      :class="(selected === 'Pages') || (page === 'fileManager' || page === 'pricingTables' || page === 'blank' || page === 'page404' || page === 'page500' || page === 'page503' || page === 'success' || page === 'faq' || page === 'comingSoon' || page === 'maintenance') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                      class="bx bxs-file"
                    ></i>

                    <span
                      class="menu-item-text"
                      :class="sidebarToggle ? 'lg:hidden' : ''"
                    >
                      Pages
                    </span>

                    <i
                      class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 bx bx-chevron-down"
                      :class="[(selected === 'Pages') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                    ></i>
                  </a>

                  <div
                    class="translate transform overflow-hidden"
                    :class="(selected === 'Pages') ? 'block' :'hidden'"
                  >
                    <ul
                      :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                      class="menu-dropdown mt-2 flex flex-col gap-1 pl-9"
                    >
                      <li>
                        <a
                          href="file-manager.html"
                          class="menu-dropdown-item group"
                          :class="page === 'fileManager' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          File Manager
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'fileManager' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="pricing-tables.html"
                          class="menu-dropdown-item group"
                          :class="page === 'pricingTables' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Pricing Tables
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'pricingTables' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="faq.html"
                          class="menu-dropdown-item group"
                          :class="page === 'faq' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Faq's
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'faq' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="blank.html"
                          class="menu-dropdown-item group"
                          :class="page === 'blank' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Blank Page
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'blank' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="404.html"
                          class="menu-dropdown-item group"
                          :class="page === 'page404' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          404 Error
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'page404' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="500.html"
                          class="menu-dropdown-item group"
                          :class="page === 'page500' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          500 Error
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'page500' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="503.html"
                          class="menu-dropdown-item group"
                          :class="page === 'page503' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          503 Error
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'page503' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="coming-soon.html"
                          class="menu-dropdown-item group"
                          :class="page === 'comingSoon' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Coming Soon
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'comingSoon' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="maintenance.html"
                          class="menu-dropdown-item group"
                          :class="page === 'termsCondition' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Maintenance
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'termsCondition' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                      <li>
                        <a
                          href="success.html"
                          class="menu-dropdown-item group"
                          :class="page === 'success' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                        >
                          Success
                          <span class="absolute right-3 flex items-center gap-1">
                            <span
                              class="menu-dropdown-badge"
                              :class="page === 'success' ? 'menu-dropdown-badge-active' : 'menu-dropdown-badge-inactive'"
                            >
                              Pro
                            </span>
                          </span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </li> --}}
                    </ul>
                </div>
            </nav>
        </div>
    </aside>
