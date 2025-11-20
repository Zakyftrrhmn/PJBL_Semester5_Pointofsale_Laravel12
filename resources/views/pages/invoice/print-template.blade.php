<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $penjualan->kode_penjualan }}</title>

    <style>
        /* === UMUM === */
        @page {
            /* Margin standar untuk dokumen A4 */
            margin: 15px 20px;
        }

        body {
            /* Menggunakan Times New Roman untuk nuansa formal/klasik */
            font-family: 'Courier New', monospace;
            font-size: 12px;
            /* Ukuran teks dasar: 10pt (normal) */
            color: #000;
            margin: 0;
            padding: 10px 25px;
            line-height: 1.4;
        }

        .wrapper {
            width: 100%;
            margin: 0 auto;
        }

        /* === KOP SURAT === */
        .kop-surat {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
        }

        .kop-surat-logo {
            width: 140px;
            height: auto;
            max-height: 55px;
            object-fit: contain;
        }

        .kop-surat-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: right;
            line-height: 1.2;
        }

        .kop-surat-info h1 {
            margin: 0;
            font-size: 12pt;
            /* Judul perusahaan: 12pt */
            text-transform: uppercase;
            letter-spacing: 0.3px;
            /* Jarak huruf dikurangi agar lebih padat */
            color: #000;
        }

        .kop-surat-info p {
            margin: 1px 0;
            font-size: 8pt;
            /* Info kontak: 8pt (paling kecil) */
            line-height: 1.2;
            color: #333;
        }

        /* === HEADER FAKTUR === */
        .header {
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .header h2 {
            margin: 0;
            font-size: 14pt;
            /* Judul FAKTUR PENJUALAN: 14pt (Menonjol) */
            text-transform: uppercase;
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding: 0 5px 2px;
        }

        .header p {
            margin: 3px 0 0;
            font-size: 9pt;
            /* Nomor Faktur: 9pt */
            font-weight: normal;
        }

        /* === INFORMASI TRANSAKSI (Pelanggan & Tanggal) === */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9.5pt;
            /* Info Transaksi: 9.5pt */
        }

        .info-table tr td:nth-child(1),
        .info-table tr td:nth-child(3) {
            width: 12%;
            font-weight: bold;
        }

        .info-table tr td:nth-child(2),
        .info-table tr td:nth-child(4) {
            width: 38%;
        }

        .section-title {
            margin-top: 5px;
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1px solid #000;
            display: block;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        /* === TABEL BARANG === */
        table.daftar {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            font-size: 9.5pt;
            /* Isi tabel: 9.5pt */
        }

        table.daftar th,
        table.daftar td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        table.daftar th {
            background-color: #f5f5f5;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.daftar td {
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* === TOTAL === */
        .total-section {
            margin-top: 0px;
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .total-section td {
            padding: 4px 6px;
        }

        .total-section tr td:first-child {
            width: 80%;
            text-align: right;
            font-weight: bold;
        }

        /* Menghapus border atas agar menyambung mulus dari tabel barang */
        .total-section tr:first-child td,
        .total-section tr:nth-child(2) td {
            border: none;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }

        /* Penyorotan Kuat pada Total Bayar (Kesan Resmi) */
        .total-section tr:last-child td {
            border: none;
            /* Hapus semua border default */
            border-top: 1px dashed #555;
            /* Garis putus-putus di atas Total Bayar */
            border-bottom: 3px double #000;
            /* Garis ganda di bawah, sangat formal */
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            background-color: #eee;
            font-size: 11pt;
            font-weight: bold;
        }


        .terbilang {
            font-size: 9pt;
            margin-top: 5px;
            font-style: italic;
            padding-left: 5px;
        }

        .footer-note {
            font-size: 8.5pt;
            margin-top: 10px;
            padding-left: 5px;
            color: #555;
        }

        /* === TANDA TANGAN === */
        .ttd-section {
            width: 100%;
            margin-top: 30px;
            text-align: right;
            page-break-inside: avoid;
        }

        .ttd-column {
            display: inline-block;
            width: 200px;
            text-align: center;
            font-size: 9.5pt;
        }

        .ttd-column p {
            margin: 0;
        }

        .ttd-box {
            height: 50px;
            margin: 15px 0 5px;
        }

        .ttd-jabatan {
            font-style: normal;
            font-size: 9pt;
            font-weight: normal;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        @php
            $page =
                $page ??
                (object) [
                    'logo_sidebar' => 'logo/logo-sidebar.png',
                    'nama_toko' => 'INTI PERAGA MANDIRI',
                    'jalan' => 'Jl. Jend. Ahmad Yani No.157, Tanah Datar',
                    'kota' => 'Pekanbaru',
                    'telepon' => '0813-7586-6604',
                    'nama_pemilik' => 'Pemilik Toko',
                ];
        @endphp

        {{-- === KOP SURAT === --}}
        <div class="kop-surat">
            {{-- Container Logo --}}
            <div class="kop-surat-logo-container">
                @php
                    $path = storage_path('app/public/' . ($page->logo_sidebar ?? 'logo/logo-sidebar.png'));
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = @file_get_contents($path);
                    $base64 = $data ? 'data:image/' . $type . ';base64,' . base64_encode($data) : '';
                @endphp

                @if ($base64)
                    <img src="{{ $base64 }}" alt="Logo Toko" class="kop-surat-logo">
                @else
                    <img src="{{ public_path('assets/images/logo/logo-sidebar.png') }}" class="kop-surat-logo"
                        alt="Logo Toko">
                @endif
            </div>
            {{-- Container Info Perusahaan --}}
            <div class="kop-surat-info">
                <h1>{{ $page && $page->nama_toko ? strtoupper($page->nama_toko) : 'INTI PERAGA MANDIRI' }}</h1>
                <p>
                    {{ $page->jalan ?? 'Jl. Jend. Ahmad Yani No.157, Tanah Datar' }}<br>
                    {{ $page->kota ?? 'Kec. Pekanbaru' }}<br>
                    Telp: {{ $page->telepon ?? '0813-7586-6604' }}
                </p>
            </div>
        </div>

        {{-- === HEADER FAKTUR === --}}
        <div class="header">
            {{-- Tambahkan keterangan yang sesuai --}}
            <h2>FAKTUR PENJUALAN</h2>
            <p>No. {{ $penjualan->kode_penjualan }}</p>
        </div>

        {{-- === INFORMASI TRANSAKSI === --}}
        <table class="info-table">
            <tr>
                <td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y') }}</td>
                <td>Pelanggan</td>
                <td>: {{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
            </tr>
        </table>

        {{-- === DAFTAR PEMBELIAN === --}}
        <div class="section-title">DAFTAR PEMBELIAN</div>

        <table class="daftar">
            <thead>
                <tr>
                    <th style="width: 15%;">Kode</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 15%;">Harga Satuan</th>
                    <th style="width: 10%;">Qty</th>
                    {{-- HANYA TAMPILKAN KOLOM DISKON JIKA isDiscountApplied=true --}}
                    @if ($isDiscountApplied)
                        <th style="width: 10%;">Diskon Item (%)</th>
                    @endif
                    {{-- Sesuaikan lebar kolom Total --}}
                    <th style="width: {{ $isDiscountApplied ? '15%' : '25%' }};">Total Item</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($penjualan->detailPenjualans as $detail)
                    @php
                        // Hitung total tanpa diskon
                        $totalItemMurni = $detail->harga_satuan * $detail->qty;

                        // Kalau print MURNI -> pakai total asli
                        if ($item_total_type === 'MURNI') {
                            $totalItemDisplay = $totalItemMurni;
                        } else {
                            // Kalau print DISKON, hitung total setelah diskon
                            $diskonPersen = $detail->diskon_percent ?? 0;
                            $diskonNilai = ($diskonPersen / 100) * $totalItemMurni;
                            $totalItemDisplay = $totalItemMurni - $diskonNilai;
                        }
                    @endphp

                    <tr>
                        <td>{{ $detail->produk->kode_produk ?? '-' }}</td>
                        <td>{{ $detail->produk->nama_produk }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $detail->qty }}</td>

                        {{-- Tampilkan kolom diskon hanya jika ada diskon --}}
                        @if ($isDiscountApplied)
                            <td class="text-center">{{ number_format($detail->diskon_percent ?? 0, 0) }}%</td>
                        @endif

                        <td class="text-right">Rp {{ number_format($totalItemDisplay, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        {{-- === TOTAL & DISKON === --}}
        <table class="total-section">
            <tr>
                {{-- Label menyesuaikan dengan Total Harga Awal yang dikirim Controller --}}
                <td>Total Harga</td>
                <td class="text-right">Rp {{ number_format($subTotalAwal, 0, ',', '.') }}</td>
            </tr>

            {{-- HANYA TAMPILKAN DISKON TRANSAKSI jika isDiscountApplied=true DAN diskon nominal > 0 --}}
            @if ($isDiscountApplied && $penjualan->diskon_nominal > 0)
                <tr>
                    <td>Diskon Transaksi ({{ number_format($penjualan->diskon_percent ?? 0, 0) }}%)</td>
                    <td class="text-right">- Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</td>
                </tr>
            @endif


            <tr>
                <td>TOTAL BAYAR</td>
                <td class="text-right">
                    Rp
                    {{ number_format($totalFinal, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        {{-- === TERBILANG === --}}
        <p class="terbilang">
            Terbilang:
            *{{ ucwords(\App\Helpers\Terbilang::make($totalFinal, ' Rupiah')) }}*
        </p>

        {{-- === CATATAN === --}}
        <div class="footer-note">
            Catatan:<br>
            Barang yang sudah dibeli tidak dapat dikembalikan/dipertukarkan.
        </div>

        {{-- === TANDA TANGAN === --}}
        <div class="ttd-section">
            <div class="ttd-column">
                <p>{{ $page->kota ?? 'Pekanbaru' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Hormat Kami,</p>
                <div class="ttd-box"></div>
                <p><strong>{{ $page->nama_pemilik ?? ($penjualan->user->name ?? 'Kasir') }}</strong></p>
                <p class="ttd-jabatan">Pemilik / Kasir</p>
            </div>
        </div>
    </div>
</body>

</html>
