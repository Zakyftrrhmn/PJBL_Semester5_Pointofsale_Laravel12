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
                        <img class="" src="{{ asset('assets/images/logo/logo.svg') }}" alt="Logo" />
                        <img class="hidden" src="{{ asset('assets/images/logo/logo-dark.svg') }}" alt="Logo" />
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

                        <div class="relative" x-data="{ dropdownOpen: false, notifying: true }" @click.outside="dropdownOpen = false">
                            <button
                                class="hover:text-dark-900 relative flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                @click.prevent="dropdownOpen = ! dropdownOpen; notifying = false">
                                <span :class="!notifying ? 'hidden' : 'flex'"
                                    class="absolute top-0.5 right-0 z-1 h-2 w-2 rounded-full bg-orange-400">
                                    <span
                                        class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
                                </span>
                                <i class="fill-current bx bxs-bell"></i>
                            </button>

                            <div x-show="dropdownOpen"
                                class="shadow-theme-lg absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 sm:w-[361px] lg:right-0">
                                <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3">
                                    <h5 class="text-lg font-semibold text-gray-800">
                                        Notification
                                    </h5>

                                    <button @click="dropdownOpen = false" class="text-gray-500">
                                        <i class="fill-current bx bx-x"></i>
                                    </button>
                                </div>

                                <ul class="custom-scrollbar flex h-auto flex-col overflow-y-auto">
                                    <li>
                                        <a class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100"
                                            href="#">
                                            <span class="relative z-1 block h-10 w-full max-w-10 rounded-full">
                                                <img src="{{ asset('assets/images/user/user-02.jpg') }}" alt="User"
                                                    class="overflow-hidden rounded-full" />
                                                <span
                                                    class="bg-success-500 absolute right-0 bottom-0 z-10 h-2.5 w-full max-w-2.5 rounded-full border-[1.5px] border-white"></span>
                                            </span>

                                            <span class="block">
                                                <span class="text-theme-sm mb-1.5 block text-gray-500">
                                                    <span class="font-medium text-gray-800">Terry Franci</span>
                                                    requests permission to change
                                                    <span class="font-medium text-gray-800">Project - Nganter App</span>
                                                </span>

                                                <span class="text-theme-xs flex items-center gap-2 text-gray-500">
                                                    <span>Project</span>
                                                    <span class="h-1 w-1 rounded-full bg-gray-400"></span>
                                                    <span>5 min ago</span>
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>

                                <a href="#"
                                    class="text-theme-sm shadow-theme-xs mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800">
                                    View All Notification
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <a class="flex items-center text-gray-700" href="#"
                            @click.prevent="dropdownOpen = ! dropdownOpen">
                            <span class="mr-3 h-9 w-9 overflow-hidden rounded-full">
                                <img src="{{ asset('assets/images/user/owner.jpg') }}" alt="User" />
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

                            <ul class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3">
                                <li>
                                    <a href="profile.html"
                                        class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                        <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-user"></i>
                                        Edit profile
                                    </a>
                                </li>
                                <li>
                                    <a href="messages.html"
                                        class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                        <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-cog"></i>
                                        Account settings
                                    </a>
                                </li>
                                <li>
                                    <a href="settings.html"
                                        class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700">
                                        <i class="fill-gray-500 group-hover:fill-gray-700 bx bx-help-circle"></i>
                                        Support
                                    </a>
                                </li>
                            </ul>
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
