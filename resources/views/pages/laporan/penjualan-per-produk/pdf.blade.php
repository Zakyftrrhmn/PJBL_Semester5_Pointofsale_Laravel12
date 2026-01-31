<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan Per Produk - {{ $periode }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 9pt;
            color: #222;
        }

        .kop-surat {
            border-bottom: 3px solid #000;
            margin-bottom: 10px;
            padding-bottom: 6px;
        }

        .kop-surat-logo {
            width: 110px;
            float: left;
        }

        .kop-surat-info {
            float: right;
            text-align: right;
            width: calc(100% - 130px);
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 8px 0 10px;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #444;
            padding: 4px;
            vertical-align: top;
        }

        .data-table th {
            background: #dbe4f0;
            text-align: center;
        }

        /* ⬇⬇ PENTING BANGET */
        .col-no {
            width: 4%;
            text-align: center;
        }

        .col-tgl {
            width: 9%;
            text-align: center;
        }

        .col-produk {
            width: 32%;
        }

        /* <<< DIPERLEBAR */
        .col-kat {
            width: 12%;
            text-align: center;
        }

        .col-qty {
            width: 6%;
            text-align: center;
        }

        .col-uang {
            width: 12%;
            text-align: right;
        }

        .produk-nama {
            font-weight: bold;
            word-wrap: break-word;
            white-space: normal;
        }

        .produk-kode {
            font-size: 7.5pt;
            color: #666;
        }

        .total-row td {
            font-weight: bold;
            background: #eef3f8;
            border-top: 2px solid #000;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="kop-surat clearfix">
        <img src="{{ $page && $page->logo_sidebar ? public_path('storage/' . $page->logo_sidebar) : public_path('assets/images/logo/logo-sidebar.png') }}"
            class="kop-surat-logo">
        <div class="kop-surat-info">
            <h3 style="margin:0">{{ $page->nama_toko ?? 'NAMA TOKO' }}</h3>
            <p style="margin:0;font-size:8pt;">
                {{ $page->jalan ?? '' }}<br>
                Telp: {{ $page->telepon ?? '-' }} | Email: {{ $page->email ?? '-' }}
            </p>
        </div>
    </div>

    <div class="report-title">LAPORAN PENJUALAN</div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Tanggal</th>
                <th class="col-produk">Nama Produk</th>
                <th class="col-kat">Kategori</th>
                <th class="col-qty">Qty</th>
                <th class="col-uang">Subtotal</th>
                <th class="col-uang">Modal</th>
                <th class="col-uang">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailPenjualans as $i => $d)
                @php
                    $modal = $d->qty * ($d->produk->harga_beli ?? 0);
                    $laba = $d->subtotal - $modal;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($d->penjualan->tanggal_penjualan)->format('d/m/y') }}</td>
                    <td class="col-produk">
                        <div class="produk-nama">{{ $d->produk->nama_produk }}</div>
                        <div class="produk-kode">{{ $d->produk->kode_produk }}</div>
                    </td>
                    <td class="text-center">{{ $d->produk->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">{{ $d->qty }}</td>
                    <td class="text-right">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($modal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($laba, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">GRAND TOTAL</td>
                <td class="text-center">{{ number_format($total_qty, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($total_subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($total_modal, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($total_laba, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
