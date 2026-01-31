{{-- Modal Content untuk Preview Barcode --}}
<div class="space-y-4">
    @if (isset($barcodeData) && count($barcodeData) > 0)
        @foreach ($barcodeData as $data)
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="mb-3">
                    <h4 class="text-sm font-semibold text-gray-800">{{ $data['nama_produk'] }}</h4>
                    <p class="text-xs text-gray-600">Kode: {{ $data['kode_produk'] }}</p>
                    <p class="text-xs text-gray-600">Jumlah: {{ $data['qty'] }} pcs</p>
                </div>

                {{-- Grid Barcode --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @for ($i = 0; $i < $data['qty']; $i++)
                        <div class="bg-white border border-gray-300 rounded p-3 text-center">
                            <div class="barcode-wrapper mb-2">
                                {!! $data['barcode_html'] !!}
                            </div>
                            <p class="text-xs font-medium text-gray-700 mt-1">{{ $data['kode_produk'] }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $data['nama_produk'] }}</p>
                        </div>
                    @endfor
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center p-8">
            <i class='bx bx-barcode text-5xl text-gray-300'></i>
            <p class="text-gray-500 mt-2">Tidak ada data barcode</p>
        </div>
    @endif
</div>

<style>
    /* Style untuk barcode agar terlihat rapi */
    .barcode-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60px;
    }

    .barcode-wrapper svg {
        max-width: 100%;
        height: auto;
    }
</style>
