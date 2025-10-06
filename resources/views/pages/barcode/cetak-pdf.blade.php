{{-- resources/views/pages/barcode/cetak-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Barcode PDF</title>
    @php use Milon\Barcode\Facades\DNS1DFacade as DNS1D; @endphp

    {{-- PERHATIAN: Pastikan DomPDF dikonfigurasi untuk mengizinkan gambar jarak jauh/base64 (DOMPDF_ENABLE_REMOTE = true) --}}

    <style>
        @page {
            margin: 8mm;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .barcode-container-print {
            width: 100%;
            /* Lebar penuh */
            display: block;
        }

        .barcode-box {
            width: 45mm;
            height: auto;
            border: 1px solid #ddd;
            margin: 2mm 1mm;
            padding: 1mm;
            text-align: center;
            float: left;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .clear-float {
            clear: both;
        }

        .barcode-box .title {
            font-size: 7px;
            font-weight: bold;
            line-height: 1;
            max-height: 10px;
            overflow: hidden;
            margin-bottom: 1px;
        }

        .barcode-box .barcode {
            margin: 1px auto;
            height: 15px;
            display: block;
        }

        .barcode-box .barcode img {
            max-width: 100%;
            height: 15px;
            display: block;
            margin: 0 auto;
        }

        .barcode-box .code {
            font-size: 7px;
            font-weight: bold;
            line-height: 1;
        }
    </style>
</head>

<body>
    <div class="barcode-container-print">
        @php
            $counter = 0; // Inisialisasi counter untuk mengatur 4 kolom
        @endphp

        @foreach ($produks as $produk)
            @php
                $qty = $jumlahData[$produk->id] ?? 1;

                // GENERATE BARCODE sebagai PNG Base64
                // C128, lebar 1.2, tinggi 15
                $barcodeBase64 = DNS1D::getBarcodePNG($produk->kode_produk, 'C128', 1.2, 15);
            @endphp

            {{-- Ulangi pencetakan sebanyak QTY --}}
            @for ($i = 0; $i < $qty; $i++)
                {{-- Logic PHP untuk Clear Float setiap 4 Box --}}
                @if ($counter > 0 && $counter % 4 == 0)
                    <div class="clear-float"></div>
                @endif

                <div class="barcode-box">
                    <div class="title">
                        {{-- Opsi: Batasi panjang nama produk jika masih tumpang tindih --}}
                        {{-- Contoh: {{ Str::limit($produk->nama_produk, 25) }} (Jika Anda menggunakan Str Facade) --}}
                        {{ $produk->nama_produk }}
                    </div>
                    <div class="barcode">
                        {{-- Gunakan data URI untuk gambar PNG Base64 --}}
                        <img src="data:image/png;base64,{{ $barcodeBase64 }}" alt="barcode">
                    </div>
                    <div class="code">{{ $produk->kode_produk }}</div>
                </div>

                @php
                    $counter++;
                @endphp
            @endfor
        @endforeach
    </div>
</body>

</html>
