<!DOCTYPE html>
<html>

<head>
    <title>Data Produk</title>
    <style>
        /* === SETTING HALAMAN A4 === */
        @page {
            size: A4;
            margin: 20mm 15mm 25mm 15mm;
            /* atas, kanan, bawah, kiri */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 10pt;
        }

        /* === HEADER / KOP SURAT === */
        .kop-surat {
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
            margin-bottom: 25px;
        }

        .kop-surat-logo {
            width: 200px;
            height: auto;
            max-height: 50px;
            object-fit: contain;
            float: left;
        }

        .kop-surat-info {
            float: right;
            text-align: right;
            width: calc(100% - 220px);
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
            line-height: 1.5;
            color: #555;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* === JUDUL LAPORAN === */
        .report-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        /* === TABEL DATA === */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9pt;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 6px 5px;
            text-align: left;
        }

        th {
            background-color: #dbe4f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* === JIKA HALAMAN TERPISAH (PAGE BREAK) === */
        tr {
            page-break-inside: avoid;
        }

        /* === TANDA TANGAN === */
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
    {{-- === KOP SURAT === --}}
    <div class="kop-surat clearfix">
        {{-- LOGO TOKO --}}
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

    {{-- === JUDUL LAPORAN === --}}
    <div class="report-title">DAFTAR PRODUK</div>

    {{-- === TABEL DATA PRODUK === --}}
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Kode</th>
                <th width="20%">Nama Produk</th>
                <th width="10%">Kategori</th>
                <th width="6%">Stok</th>
                <th width="10%">Modal</th>
                <th width="10%">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $no => $produk)
                <tr>
                    <td class="text-center">{{ $no + 1 }}</td>
                    <td>{{ $produk->kode_produk }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">{{ $produk->stok_produk }}</td>
                    <td class="text-right">Rp{{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px;">Tidak ada data produk yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- === TANDA TANGAN === --}}
    <div class="ttd-section clearfix">
        <div class="ttd-column">
            <p>
                {{ $page->kota ? $page->kota : 'isi kota di informasi toko' }},
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 5px;">Dibuat oleh,</p>
            <div class="ttd-box">
                {{ $page && $page->nama_pemilik ? $page->nama_pemilik : 'Masukkan nama pemilik toko di informasi toko' }}
            </div>
            <p class="ttd-jabatan">Pemilik</p>
        </div>
    </div>
</body>

</html>
