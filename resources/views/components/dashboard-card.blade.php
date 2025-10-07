<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md transition duration-300 hover:shadow-lg">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
        <span class="rounded-full p-2 {{ $icon_bg }} text-white">
            <i class="bx {{ $icon }} text-xl"></i>
        </span>
    </div>

    <p class="mt-4 text-lg font-extrabold text-gray-900 whitespace-nowrap">
        {{ $value }}
    </p>

    {{-- Info Waktu Filter --}}
    <p class="mt-1 text-xs font-medium {{ $color }} rounded-lg inline-block px-2 py-0.5">
        @if ($filter == 'daily')
            Hari Ini
        @elseif ($filter == 'monthly')
            Bulan Ini
        @elseif ($filter == 'yearly')
            Tahun Ini
        @else
            Semua Data
        @endif
    </p>
</div>
