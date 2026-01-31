<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan - {{ $periode }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* --- KOP SURAT --- */
        .kop-surat {
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
            margin-bottom: 25px;
        }

        .kop-surat-logo {
            width: 150px;
            height: auto;
            max-height: 60px;
            object-fit: contain;
            float: left;
        }

        .kop-surat-info {
            float: right;
            text-align: right;
            width: calc(100% - 180px);
        }

        .kop-surat h1 {
            margin: 0;
            font-size: 16pt;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .kop-surat p {
            margin: 0;
            font-size: 9pt;
            line-height: 1.4;
            color: #555;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* --- JUDUL & INFO --- */
        .report-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        .report-info-container {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-item {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        /* --- TABEL DATA (Dioptimalkan untuk Landscape) --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
            table-layout: fixed;
            /* Mengunci lebar kolom */
        }

        .data-table th,
        .data-table td {
            border: 1px solid #444;
            padding: 6px 4px;
            word-wrap: break-word;
            /* Mencegah teks meluber */
        }

        .data-table th {
            background-color: #dbe4f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* --- WARNA BARIS --- */
        .total-row td {
            font-weight: bold;
            background-color: #eaf1f7;
            border-top: 3px double #333 !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-complete {
            color: #107c10;
            font-weight: bold;
        }

        .status-retur {
            color: #cc0000;
            font-weight: bold;
        }

        /* --- TANDA TANGAN --- */
        .ttd-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .ttd-column {
            width: 250px;
            float: right;
            text-align: center;
            font-size: 10pt;
        }

        .ttd-box {
            height: 60px;
            margin-top: 10px;
        }

        .pelanggan-nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="kop-surat clearfix">
        <img src="{{ $page && $page->logo_sidebar ? public_path('storage/' . $page->logo_sidebar) : public_path('assets/images/logo/logo-sidebar.png') }}"
            class="kop-surat-logo" alt="Logo">

        <div class="kop-surat-info">
            <h1>{{ $page->nama_toko ?? 'NAMA TOKO' }}</h1>
            <p>
                {{ $page->jalan ?? '' }}{{ $page->kelurahan ? ', ' . $page->kelurahan : '' }}<br>
                {{ $page->kecamatan ? 'Kec. ' . $page->kecamatan : '' }}{{ $page->kota ? ', ' . $page->kota : '' }}<br>
                Telp: {{ $page->telepon ?? '-' }} | Email: {{ $page->email ?? '-' }}
            </p>
        </div>
    </div>

    <div class="report-title">LAPORAN PENJUALAN</div>

    <div class="report-info-container">
        <div class="info-item"><strong>Periode:</strong> {{ $periode }}</div>
        {{-- <div class="info-item"><strong>Filter Status:</strong> {{ $status_label }}</div> --}}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10px">No</th>
                <th width="100px">Invoice</th>
                <th width="90px">Tanggal</th>
                <th width="90px" class="pelanggan-nowrap">Pelanggan</th>
                <th width="100px">Bayar</th>
                <th width="90px">Modal</th>
                <th width="90px">Laba</th>
                <th width="70px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualans as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $p->kode_penjualan }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d/m/Y') }}</td>
                    <td>{{ $p->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                    <td class="text-right">{{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #854d0e">{{ number_format($p->total_modal, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #166534">
                        {{ number_format($p->laba, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($p->returPenjualans->isNotEmpty())
                            <span class="status-retur">RETUR</span>
                        @else
                            <span class="status-complete">OK</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">GRAND TOTAL:</td>
                <td class="text-right">{{ number_format($total_bayar_all, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($total_modal_all, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($total_laba_all, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="ttd-section clearfix">
        <div class="ttd-column">
            <p>{{ $page->kota ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Dibuat oleh,</p>
            <div class="ttd-box"></div>
            <p style="font-weight: bold; text-decoration: underline;">{{ $page->nama_pemilik ?? 'Nama Pemilik' }}</p>
            <p>Pemilik Toko</p>
        </div>
    </div>

</body>

</html>
