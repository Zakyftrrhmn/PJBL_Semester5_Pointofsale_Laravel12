@extends('layouts.layout')
@section('title', 'Data Kategori')
@section('content')

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800">
                    @yield('title')
                </h3>
            </div>
            <div class="p-5 border-t border-gray-100 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="font-medium text-gray-500 text-theme-xs">
                                                User
                                            </p>
                                        </div>
                                    </th>
                                    <th class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="font-medium text-gray-500 text-theme-xs">
                                                Project Name
                                            </p>
                                        </div>
                                    </th>
                                    <th class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="font-medium text-gray-500 text-theme-xs">
                                                Team
                                            </p>
                                        </div>
                                    </th>
                                    <th class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="font-medium text-gray-500 text-theme-xs">
                                                Status
                                            </p>
                                        </div>
                                    </th>
                                    <th class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="font-medium text-gray-500 text-theme-xs">
                                                Budget
                                            </p>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 overflow-hidden rounded-full">
                                                    <img src="{{ asset('assets/images/user/user-17.jpg') }}"
                                                        alt="brand" />
                                                </div>

                                                <div>
                                                    <span class="block font-medium text-gray-800 text-theme-sm">
                                                        Lindsey Curtis
                                                    </span>
                                                    <span class="block text-gray-500 text-theme-xs">
                                                        Web Designer
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="text-gray-500 text-theme-sm">
                                                Agency Website
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <div class="flex -space-x-2">
                                                <div class="w-6 h-6 overflow-hidden border-2 border-white rounded-full">
                                                    <img src="{{ asset('assets/images/user/user-22.jpg') }}"
                                                        alt="user" />
                                                </div>
                                                <div class="w-6 h-6 overflow-hidden border-2 border-white rounded-full">
                                                    <img src="{{ asset('assets/images/user/user-23.jpg') }}"
                                                        alt="user" />
                                                </div>
                                                <div class="w-6 h-6 overflow-hidden border-2 border-white rounded-full">
                                                    <img src="{{ asset('assets/images/user/user-24.jpg') }}"
                                                        alt="user" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <p
                                                class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700">
                                                Active
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center">
                                            <p class="text-gray-500 text-theme-sm">3.9K</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
