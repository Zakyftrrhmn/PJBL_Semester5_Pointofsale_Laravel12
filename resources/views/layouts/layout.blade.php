  <!doctype html>
  <html lang="en">
  @include('components.head')

  <body x-data="{ page: 'basicTables', 'loaded': true, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }" class="bg-[#F7F7F7]">
      <div class="flex h-screen overflow-hidden ">

          @include('components.sidebar')

          <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
              <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                  class="fixed w-full h-screen z-9 bg-gray-900/50"></div>

              @include('components.header')

              <main>
                  <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6 ">
                      <div>
                          <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                              <h2 class="text-xl font-semibold text-gray-800">@yield('title')</h2>

                              <nav>
                                  <ol class="flex items-center gap-1.5">
                                      <li>
                                          <a class="inline-flex items-center gap-1.5 text-sm text-gray-500"
                                              href="index.html">
                                              Home
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
      @vite('resources/js/app.js')
  </body>

  </html>
