<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
        }

        .container {
            width: 100%;
        }

        .barcode-card {
            width: 24%;
            /* Pas 4 kolom */
            float: left;
            margin-bottom: 10px;
            padding: 10px 5px;
            text-align: center;
            box-sizing: border-box;
            border: 1px dashed #ccc;
            /* Garis bantu potong */
        }

        .product-name {
            font-size: 7pt;
            font-weight: bold;
            height: 20px;
            overflow: hidden;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .barcode-image img {
            width: 100%;
            /* Agar barcode memenuhi lebar kotak */
            height: 30px;
        }

        .product-code {
            font-size: 8pt;
            margin-top: 5px;
            font-family: monospace;
            font-weight: bold;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        @php $counter = 0; @endphp
        @foreach ($produks as $produk)
            @php
                $qty = $jumlahData[$produk->id] ?? 1;
                // Menggunakan factor 2.0 agar garis lebih tebal & mudah di-scan iWore
                $barcodeBase64 = \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($produk->kode_produk, 'C128', 2, 33);
            @endphp

            @for ($i = 0; $i < $qty; $i++)
                <div class="barcode-card">
                    <div class="product-name">{{ $produk->nama_produk }}</div>
                    <div class="barcode-image">
                        <img src="data:image/png;base64,{{ $barcodeBase64 }}">
                    </div>
                    <div class="product-code">{{ $produk->kode_produk }}</div>
                </div>
                @php $counter++; @endphp
                @if ($counter % 4 == 0)
                    <div class="clear"></div>
                @endif
            @endfor
        @endforeach
    </div>
</body>

</html>
