<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pembelian - {{ $periode }}</title>
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

        /* --- STYLING INFORMASI LAPORAN --- */
        .report-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        Berikut adalah kode CSS lengkap yang sudah diperbaiki untuk memastikan bagian informasi laporan (.report-info-container) rapi dan rata. Saya menggunakan kombinasi peningkatan lebar label dan Flexbox pada level yang lebih tinggi untuk penyelarasan yang konsisten. Kode CSS Lengkap dengan Perbaikan Saya telah menyertakan semua CSS Anda dan melakukan penyesuaian utama pada bagian
        /* --- STYLING INFORMASI LAPORAN --- */
        untuk membuat label dan nilai bersebelahan dengan rapi. CSS <style>

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

        /* --- STYLING INFORMASI LAPORAN --- */
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
            margin-bottom: 20px;
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
        }

        .info-item {
            display: block;
            font-size: 10pt;
            line-height: 1.5;
            flex: 1 1 50%;
            padding: 2px 0;
        }

        .info-label {
            display: inline-block;
            width: auto;
        }

        /* --- STYLING TABEL --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }

        .data-table th {
            background-color: #dbe4f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        /* --- STYLING BARIS TOTAL --- */
        .total-row td {
            font-weight: bold;
            background-color: #eaf1f7;
            border-top: 3px double #333 !important;
            font-size: 10pt;
        }

        /* --- STYLING ALIGNMENT KHUSUS --- */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* --- STYLING STATUS --- */
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

        /* --- STYLING TANDA TANGAN (TTD) --- */
        .ttd-section {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .ttd-column {
            width: 300px;
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

        .status-partial-retur {
            color: #e67e22;
            /* Orange color */
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>

<body>
    {{-- KOP SURAT --}}
    <div class="kop-surat clearfix">
        {{-- LOGO SEBELAH KIRI --}}
        {{-- Gunakan public_path() jika asset() tidak berfungsi saat generate PDF --}}
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

    <div class="report-title">LAPORAN PEMBELIAN</div>

    {{-- INFORMASI LAPORAN (PERIODE & STATUS FILTER) --}}
    <div class="report-info-container">
        <div class="info-item">
            {{-- Menambahkan titik dua (:) secara eksplisit di sini atau di CSS --}}
            <span class="info-label">Periode Laporan: {{ $periode }} ({{ $preset_label }})</span>
        </div>
        {{-- <div class="info-item">
            <span class="info-label">Filter Status:{{ $status_label }}</span>
        </div> --}}
    </div>
    <div class="clearfix"></div>
    {{-- /INFORMASI LAPORAN --}}


    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">Tanggal</th>
                <th width="17%">Kode Transaksi</th>
                <th width="30%">Pemasok</th>
                <th width="15%">Total Bayar</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembelians as $index => $pembelian)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d F Y') }}</td>
                    <td>{{ $pembelian->kode_pembelian }}</td>
                    {{-- PASTIKAN Menggunakan relasi 'pemasok' dan null coalescing untuk menghindari error --}}
                    <td>{{ $pembelian->pemasok->nama_pemasok ?? '-' }}</td>
                    <td class="text-right">Rp{{ number_format($pembelian->sisa_total_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($pembelian->total_nilai_retur >= $pembelian->total_bayar)
                            <span class="status-retur">
                                Retur Penuh
                            </span>
                        @elseif ($pembelian->total_nilai_retur > 0)
                            <span class="status-partial-retur">
                                Retur Sebagian
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
                    <td colspan="6" class="text-center" style="padding: 15px;">Tidak ada data pembelian yang
                        ditemukan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN ({{ $pembelians->count() }} Transaksi):</td>
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
