{{-- resources/views/pages/barcode/modal-content.blade.php --}}

<div class="flex flex-wrap gap-2 justify-center p-4">
    @forelse ($barcodeData as $data)
        <div
            class="p-2 border rounded-lg text-center w-auto h-[96px] flex flex-col justify-between items-center bg-white shadow-sm">

            <div class="text-sm font-bold overflow-hidden whitespace-nowrap truncate w-full px-1"
                title="{{ $data['nama_produk'] }}">
                {{ $data['nama_produk'] }}
            </div>

            <div class="w-full max-h-12 flex justify-center items-center">
                {!! $data['barcode_html'] !!}
            </div>

            <div class="text-sm font-bold mt-1">
                {{ $data['kode_produk'] }}
            </div>
        </div>
    @empty
        <p class="text-gray-500">Tidak ada barcode untuk ditampilkan.</p>
    @endforelse
</div>
