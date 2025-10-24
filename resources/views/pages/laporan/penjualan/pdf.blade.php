<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan - {{ $periode }}</title>
    <style>
        /* Menggunakan font yang lebih formal dan jelas untuk dokumen cetak */
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* --- STYLING HEADER DOKUMEN (KOP SURAT) --- */
        .kop-surat {
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
            /* Border lebih tebal */
            margin-bottom: 25px;
        }

        .kop-surat-logo {
            width: 200px;
            /* Ukuran yang lebih proporsional untuk logo */
            height: auto;
            max-height: 50px;
            object-fit: contain;
            float: left;
            /* Logo mengambang di kiri */
        }

        .kop-surat-info {
            float: right;
            text-align: right;
            /* Teks rata kanan */
            width: calc(100% - 220px);
            /* Sisa ruang untuk info toko */
        }

        .kop-surat h1 {
            margin: 0;
            font-size: 16pt;
            /* Ukuran font lebih formal */
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .kop-surat p {
            margin: 0;
            font-size: 9pt;
            line-height: 1.5;
            color: #555;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* --- STYLING JUDUL LAPORAN (Menyesuaikan dengan style Laporan Pembelian) --- */
        .report-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        /* --- STYLING INFORMASI LAPORAN (Menyesuaikan dengan style Laporan Pembelian menggunakan Flexbox) --- */
        .report-info-container {
            width: 100%;
            margin-bottom: 20px;
            padding: 5px 0;
            /* Mengganti display: flex, justify-content: space-between */
        }

        /* Menggunakan float untuk penyelarasan di dokumen PDF (lebih stabil daripada flexbox) */
        .info-item {
            display: block;
            font-size: 10pt;
            line-height: 1.5;
            flex: 1 1 50%;
            padding: 2px 0;
        }



        /* --- STYLING TABEL --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            /* Ukuran font lebih kecil */
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #555;
            /* Warna border yang lebih ringan */
            padding: 8px;
            text-align: left;
        }

        .data-table th {
            background-color: #dbe4f0;
            /* Warna header yang lebih lembut */
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        /* --- STYLING BARIS TOTAL (Menyesuaikan dengan style Laporan Pembelian) --- */
        .total-row td {
            font-weight: bold;
            background-color: #eaf1f7;
            /* Warna latar total */
            border-top: 3px double #333 !important;
            /* Border total yang unik */
            font-size: 10pt;
        }

        /* --- STYLING ALIGNMENT KHUSUS --- */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* --- STYLING STATUS (Menyesuaikan dengan style Laporan Pembelian) --- */
        .status-complete {
            color: #107c10;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }

        .status-retur {
            color: #cc0000;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }

        /* --- STYLING TANDA TANGAN (TTD) (Menyesuaikan dengan style Laporan Pembelian) --- */
        .ttd-section {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .ttd-column {
            width: 300px;
            /* Lebar kolom yang lebih spesifik */
            float: right;
            text-align: center;
            font-size: 10pt;
        }

        .ttd-box {
            height: 70px;
            margin: 15px auto 5px;
            line-height: 70px;
            color: #333;
            font-weight: bold;
        }

        .ttd-line {
            display: block;
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 5px auto 0;
        }

        .ttd-jabatan {
            margin-top: 0;
            font-style: italic;
        }

        .kop-surat-info p {
            margin: 2px 0;
            line-height: 1.4;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop-surat clearfix">
        {{-- LOGO SEBELAH KIRI --}}
        {{-- GANTI DENGAN INFO TOKO ANDA YANG ASLI --}}
        <img src="{{ $page && $page->logo_sidebar ? public_path('storage/' . $page->logo_sidebar) : public_path('assets/images/logo/logo-sidebar.png') }}"
            class="kop-surat-logo" alt="Logo Toko">

        {{-- INFORMASI TOKO --}}
        <div class="kop-surat-info">
            <h1>{{ $page && $page->nama_toko ? strtoupper($page->nama_toko) : 'MASUKKAN NAMA TOKO DI INFORMASI TOKO' }}
            </h1>

            @if ($page)
                <p>
                    {{-- Baris 1: Jalan + Kelurahan --}}
                    {{ $page->jalan ?? 'Jl. Belum diisi' }}{{ $page->kelurahan ? ', ' . $page->kelurahan : '' }}<br>

                    {{-- Baris 2: Kecamatan + Kota --}}
                    {{ $page->kecamatan ? 'Kec. ' . $page->kecamatan : '' }}{{ $page->kota ? ', ' . $page->kota : '' }}<br>

                    {{-- Baris 3: Provinsi + Kode Pos --}}
                    {{ $page->provinsi ?? '' }}{{ $page->kode_pos ? ' ' . $page->kode_pos : '' }}
                </p>

                @if ($page->telepon || $page->telepon2)
                    <p>Telp: {{ $page->telepon }}{{ $page->telepon2 ? ' / ' . $page->telepon2 : '' }}</p>
                @endif

                @if ($page->email)
                    <p>Email: {{ $page->email }}</p>
                @endif
            @else
                <p>Masukkan alamat toko di informasi toko</p>
            @endif
        </div>
    </div>
    {{-- /KOP SURAT --}}

    {{-- JUDUL LAPORAN (Menggunakan class yang sudah disesuaikan) --}}
    <div class="report-title">LAPORAN PENJUALAN</div>

    {{-- INFORMASI LAPORAN (PERIODE & STATUS FILTER) (Menggunakan struktur Laporan Pembelian) --}}
    <div class="report-info-container clearfix">
        <div class="info-item">
            <span class="info-label">Periode Laporan: {{ $periode }} ({{ $preset_label }})</span>
        </div>
        {{-- <div class="info-item">
            <span class="info-label">Filter Status: {{ $status_label }}</span>
        </div> --}}
    </div>
    {{-- /INFORMASI LAPORAN --}}


    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Kode Penjualan</th>
                <th width="15%">Tanggal</th>
                <th width="30%">Pelanggan</th>
                <th width="15%">Total Bayar</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualans as $index => $penjualan)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $penjualan->kode_penjualan }}</td>
                    <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d F Y') }}</td>
                    {{-- PASTIKAN Menggunakan relasi 'pelanggan' dan null coalescing untuk menghindari error --}}
                    <td>{{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td class="text-right">Rp{{ number_format($penjualan->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($penjualan->returPenjualans->isNotEmpty())
                            <span class="status-retur">
                                Retur
                            </span>
                        @else
                            <span class="status-complete">
                                Completed
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">Tidak ada data penjualan yang
                        ditemukan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN ({{ $penjualans->count() }} Transaksi):</td>
                <td class="text-right">Rp{{ number_format($total_bayar_all, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="ttd-section clearfix">
        <div class="ttd-column">
            <p>{{ $page->kota ? $page->kota : 'isi kota di informasi toko' }},
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 5px;">Dibuat oleh,</p>
            <div class="ttd-box">
                {{-- Nama Penandatanganan --}}
                {{ $page && $page->nama_pemilik ? $page->nama_pemilik : 'Masukkan nama pemilik toko di informasi toko' }}
            </div>
            {{-- Jabatan Penandatanganan --}}
            <p class="ttd-jabatan">{{ 'Pemilik' }}</p>
        </div>
    </div>
</body>

</html>
