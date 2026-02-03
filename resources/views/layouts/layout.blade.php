<!doctype html>
<html lang="en">
@include('components.head')

<body x-data="{
    page: 'basicTables',
    loaded: true,
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false,
    showModal: false,
    deleteUrl: '',
    loading: true
}" x-init="setTimeout(() => loading = false, 100)" class="bg-[#F7F7F7]">

    <!-- GLOBAL LOADING SCREEN -->
    <div x-show="loading" x-transition.opacity.duration.100ms
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-white">
        <div class="flex flex-col items-center gap-4">
            <svg class="h-10 w-10 animate-spin text-blue-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                </path>
            </svg>
            <span class="text-sm text-gray-500">Memuat halaman...</span>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        @include('components.sidebar')

        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed inset-0 z-40 bg-gray-900/50"></div>

            @include('components.header')

            <main>
                <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                    <div>
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">@yield('title')</h2>
                                <p class="text-sm text-gray-500">@yield('subtitle')</p>
                            </div>
                            <nav>
                                <ol class="flex items-center gap-1.5">
                                    <li>
                                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500"
                                            href="{{ route('dashboard.index') }}">
                                            Dashboard
                                            <i class="stroke-current bx bx-chevron-right"></i>
                                        </a>
                                    </li>
                                    <li class="text-sm text-gray-800">@yield('title')</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('components.modal')


    @include('components.script')

    @stack('scripts') <!-- Tambahkan ini sebelum </body> -->

</body>

</html>
