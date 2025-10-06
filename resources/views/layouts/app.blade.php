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
    deleteUrl: ''
}" class="bg-[#F7F7F7] min-h-screen flex flex-col">

    <div class="flex flex-1 overflow-hidden">

        <!-- Sidebar overlay (responsive) -->
        <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
            class="fixed inset-0 z-40 bg-gray-900/50"></div>

        <!-- Main content -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

            @include('components.header2')

            <main class="flex-1">
                <div class="p-3 sm:p-4 md:p-6 max-w-screen-2xl mx-auto w-full">
                    <!-- Header Section -->
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 break-words">@yield('title')
                            </h2>
                            <p class="text-sm text-gray-500">@yield('subtitle')</p>
                        </div>
                        <nav class="text-sm">
                            <ol class="flex flex-wrap items-center gap-1.5 text-gray-500">
                                <li>
                                    <a href=""
                                        class="inline-flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                                        Dashboard
                                        <i class="bx bx-chevron-right text-base"></i>
                                    </a>
                                </li>
                                <li class="text-gray-800 font-medium">@yield('title')</li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-3 sm:p-5">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('components.modal')
    @include('components.script')

</body>

</html>
