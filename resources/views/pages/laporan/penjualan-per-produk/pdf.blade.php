<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan Per Produk - {{ $periode }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 8.5pt;
            color: #222;
        }

        .kop-surat {
            border-bottom: 3px solid #000;
            margin-bottom: 8px;
            padding-bottom: 5px;
        }

        .kop-surat-info {
            float: right;
            text-align: right;
            width: calc(100% - 105px);
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 6px 0 4px;
        }

        .report-meta {
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .report-meta span {
            display: inline-block;
            margin: 0 8px;
        }

        .report-meta .label {
            color: #888;
            font-size: 7pt;
            text-transform: uppercase;
        }

        /* ===== TABEL UTAMA ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .data-table th,
        .data-table td {
            border: 0.5px solid #bbb;
            padding: 2.5px 3px;
            vertical-align: top;
        }

        .data-table th {
            background: #dbe4f0;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            line-height: 1.3;
            color: #333;
        }

        /* ===== GROUP HEADER ===== */
        .group-header-disc {
            background: #fff3e0 !important;
            color: #e65100 !important;
        }

        .group-header-disc-trans {
            background: #ffebee !important;
            color: #c62828 !important;
        }

        .group-header-result {
            background: #e3f2fd !important;
            color: #1565c0 !important;
        }

        /* ===== KOLOM WIDTH ===== */
        .col-no {
            width: 3.2%;
            text-align: center;
        }

        .col-invoice {
            width: 8.5%;
        }

        .col-tgl {
            width: 7.5%;
            text-align: center;
        }

        .col-kode {
            width: 8%;
        }

        .col-produk {
            width: 16%;
        }

        .col-kat {
            width: 8.5%;
            text-align: center;
        }

        .col-qty {
            width: 4%;
            text-align: center;
        }

        .col-harga-sat {
            width: 6.5%;
            text-align: right;
        }

        .col-harga-kotor {
            width: 7%;
            text-align: right;
        }

        .col-disk-pct {
            width: 5%;
            text-align: center;
        }

        .col-disk-nom {
            width: 6.5%;
            text-align: right;
        }

        .col-disk-trans {
            width: 7%;
            text-align: right;
        }

        .col-subtotal {
            width: 7%;
            text-align: right;
        }

        .col-modal {
            width: 6.5%;
            text-align: right;
        }

        .col-laba {
            width: 6.5%;
            text-align: right;
        }

        /* ===== ROW STYLES ===== */
        .row-has-discount {
            background-color: #fffbf0;
        }

        .produk-nama {
            font-weight: bold;
            word-wrap: break-word;
            white-space: normal;
        }

        .produk-kode {
            font-size: 6.5pt;
            color: #666;
        }

        /* ===== DISKON BADGE ===== */
        .disc-badge {
            display: inline-block;
            background: #fff3e0;
            color: #e65100;
            font-size: 6.5pt;
            font-weight: bold;
            padding: 1px 3px;
            border-radius: 2px;
            border: 0.5px solid #ffcc80;
        }

        .disc-trans-badge {
            display: inline-block;
            background: #ffebee;
            color: #c62828;
            font-size: 6.5pt;
            font-weight: bold;
            padding: 1px 3px;
            border-radius: 2px;
            border: 0.5px solid #ef9a9a;
        }

        /* ===== STRIKETHROUGH --*/
        .text-strikethrough {
            text-decoration: line-through;
            color: #999;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .color-orange {
            color: #e65100;
        }

        .color-red {
            color: #c62828;
        }

        .color-blue {
            color: #1565c0;
        }

        .color-green {
            color: #2e7d32;
        }

        .color-gray {
            color: #999;
        }

        .font-bold {
            font-weight: bold;
        }

        /* ===== TOTAL ROW ===== */
        .total-row td {
            font-weight: bold;
            background: #eef3f8;
            border-top: 1.5px solid #000;
            font-size: 7.5pt;
        }

        .dash {
            color: #ccc;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop-surat" style="text-align:center;">
        <h3 style="margin:0; font-size: 11pt; font-weight:bold;">
            {{ $page->nama_toko ?? 'NAMA TOKO' }}
        </h3>

        <p style="margin:2px 0 0; font-size:7.5pt; color:#555; line-height:1.4;">
            {{ $page->jalan ?? '' }}<br>
            Telp: {{ $page->telepon ?? '-' }} | Email: {{ $page->email ?? '-' }}
        </p>
    </div>


    {{-- JUDUL LAPORAN --}}
    <div class="report-title">LAPORAN PENJUALAN PER PRODUK</div>
    <div class="report-meta">
        <span><span class="label">Periode:</span> {{ $periode }}</span>
        <span style="color:#ddd;">|</span>
        <span><span class="label">Status:</span> {{ $statusLabel }}</span>
        <span style="color:#ddd;">|</span>
        <span><span class="label">Unit:</span> {{ $prefixLabel }}</span>
        <span style="color:#ddd;">|</span>
        <span><span class="label">Kategori:</span> {{ $kategoriLabel }}</span>
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            {{-- ROW 1: Group Headers --}}
            <tr>
                <th class="col-no" rowspan="2">No</th>
                <th class="col-invoice" rowspan="2">Invoice</th>
                <th class="col-tgl" rowspan="2">Tanggal</th>
                <th class="col-kode" rowspan="2">Kode Produk</th>
                <th class="col-produk" rowspan="2">Nama Produk</th>
                <th class="col-kat" rowspan="2">Kategori</th>
                <th class="col-qty" rowspan="2">Qty</th>
                <th class="col-harga-sat" rowspan="2">Harga Satuan</th>
                <th class="col-harga-kotor" rowspan="2">Harga Kotor</th>
                {{-- Group: Diskon --}}
                <th colspan="3" class="group-header-disc">DISKON</th>
                {{-- Group: Hasil --}}
                <th colspan="3" class="group-header-result">HASIL</th>
            </tr>
            {{-- ROW 2: Sub-headers --}}
            <tr>
                <th class="col-disk-pct group-header-disc">Item (%)</th>
                <th class="col-disk-nom group-header-disc">Item (Rp)</th>
                <th class="col-disk-trans group-header-disc-trans">Transaksi (Rp)</th>
                <th class="col-subtotal group-header-result">Subtotal Net</th>
                <th class="col-modal" style="background:#fff3e0; color:#e65100;">Modal</th>
                <th class="col-laba" style="background:#e8f5e9; color:#2e7d32;">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($allocatedItems as $i => $row)
                @php
                    $d = $row['detail'];
                    $alloc = $row['allocated'];
                    $modal = $row['modal'];
                    $laba = $row['laba'];
                    $hasDisk = $alloc['diskon_item_nominal'] > 0 || $alloc['diskon_transaksi_item'] > 0;
                @endphp
                <tr class="{{ $hasDisk ? 'row-has-discount' : '' }}">
                    <td class="col-no text-center">{{ $i + 1 }}</td>
                    <td class="col-invoice font-bold" style="color:#3f51b5;">{{ $d->penjualan->kode_penjualan }}</td>
                    <td class="col-tgl text-center">
                        {{ \Carbon\Carbon::parse($d->penjualan->tanggal_penjualan)->format('d/m/y') }}
                    </td>
                    <td class="col-kode" style="font-family: monospace; font-size:7pt;">
                        {{ $d->produk->kode_produk ?? '-' }}
                    </td>
                    <td class="col-produk">
                        <div class="produk-nama">{{ $d->produk->nama_produk }}</div>
                        <div class="produk-kode">{{ $d->produk->kode_produk }}</div>
                    </td>
                    <td class="col-kat text-center" style="font-size:7pt;">
                        {{ $d->produk->kategori->nama_kategori ?? '-' }}
                    </td>
                    <td class="col-qty text-center font-bold">{{ $d->qty }}</td>
                    <td class="col-harga-sat text-right">
                        {{ number_format($d->harga_satuan, 0, ',', '.') }}
                    </td>

                    {{-- Harga Kotor --}}
                    <td class="col-harga-kotor text-right">
                        @if ($hasDisk)
                            <span
                                class="text-strikethrough">{{ number_format($alloc['harga_kotor'], 0, ',', '.') }}</span>
                        @else
                            {{ number_format($alloc['harga_kotor'], 0, ',', '.') }}
                        @endif
                    </td>

                    {{-- Diskon Item % --}}
                    <td class="col-disk-pct text-center" style="background:#fff8f0;">
                        @if ($alloc['diskon_item_percent'] > 0)
                            <span class="disc-badge">{{ number_format($alloc['diskon_item_percent'], 1) }}%</span>
                        @else
                            <span class="dash">—</span>
                        @endif
                    </td>

                    {{-- Diskon Item Rp --}}
                    <td class="col-disk-nom text-right color-orange" style="background:#fff8f0;">
                        @if ($alloc['diskon_item_nominal'] > 0)
                            -{{ number_format($alloc['diskon_item_nominal'], 0, ',', '.') }}
                        @else
                            <span class="dash">—</span>
                        @endif
                    </td>

                    {{-- Diskon Transaksi (Alokasi) --}}
                    <td class="col-disk-trans text-right color-red" style="background:#fff5f5;">
                        @if ($alloc['diskon_transaksi_item'] > 0)
                            -{{ number_format($alloc['diskon_transaksi_item'], 0, ',', '.') }}
                        @else
                            <span class="dash">—</span>
                        @endif
                    </td>

                    {{-- Subtotal Net --}}
                    <td class="col-subtotal text-right font-bold color-blue" style="background:#e8eeff;">
                        {{ number_format($alloc['subtotal_net'], 0, ',', '.') }}
                    </td>

                    {{-- Modal --}}
                    <td class="col-modal text-right color-orange">
                        {{ number_format($modal, 0, ',', '.') }}
                    </td>

                    {{-- Laba --}}
                    <td class="col-laba text-right font-bold {{ $laba >= 0 ? 'color-green' : 'color-red' }}">
                        {{ number_format($laba, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 20px; color: #999; font-style: italic;">
                        Tidak ada data penjualan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right" style="padding-right: 6px;">GRAND TOTAL</td>
                <td class="text-center">{{ number_format($total_qty, 0, ',', '.') }}</td>
                <td colspan="2"></td>
                <td colspan="3"></td>
                <td class="text-right color-blue">{{ number_format($total_subtotal, 0, ',', '.') }}</td>
                <td class="text-right color-orange">{{ number_format($total_modal, 0, ',', '.') }}</td>
                <td class="text-right {{ $total_laba >= 0 ? 'color-green' : 'color-red' }}">
                    {{ number_format($total_laba, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN --}}
    <div style="margin-top: 12px; font-size: 7pt; color: #888; border-top: 0.5px solid #ddd; padding-top: 6px;">
        <strong>Catatan:</strong>
        Kolom "Diskon Transaksi" menunjukkan alokasi diskon transaksi secara proporsional berdasarkan kontribusi
        subtotal masing-masing produk.
        "Subtotal Net" adalah nilai final setelah semua diskon (per-item dan transaksi) diterapkan.
        Laba = Subtotal Net - Modal.
    </div>

</body>

</html>
