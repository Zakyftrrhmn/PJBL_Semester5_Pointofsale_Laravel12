<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $penjualan->kode_penjualan }}</title>

    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: "Times New Roman", Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0 auto;
            width: 90%;
        }

        .wrapper {
            width: 100%;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 6px;
            padding-bottom: 3px;
            line-height: 1.2;
        }

        .header h2 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 10px;
        }

        .info-table {
            width: 100%;
            margin-top: 5px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
        }

        .section-title {
            margin-top: 5px;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #000;
            display: inline-block;
        }

        table.daftar {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        table.daftar th,
        table.daftar td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 10px;
        }

        table.daftar th {
            background-color: #f5f5f5;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            margin-top: 5px;
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .total-section td {
            padding: 2px 4px;
        }

        .terbilang {
            font-size: 10px;
            margin-top: 3px;
        }

        .footer-note {
            font-size: 9px;
            margin-top: 8px;
        }

        .signature {
            margin-top: 25px;
            width: 100%;
            font-size: 10px;
        }

        .signature td {
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        {{-- HEADER TOKO --}}
        <div class="header">
            <strong>SARANA PERAGA</strong>
            <p>JALAN PALANGKARAYA BARU NO.2, MEDAN<br>Telp: 0813-7586-6604</p>
            <h2>FAKTUR PENJUALAN</h2>
            <p>No. {{ $penjualan->kode_penjualan }}</p>
        </div>

        {{-- INFORMASI TRANSAKSI --}}
        <table class="info-table">
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y') }}</td>
                <td><strong>Pelanggan</strong></td>
                <td>: {{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
            </tr>
        </table>

        {{-- DAFTAR PEMBELIAN --}}
        <div class="section-title">Daftar Pembelian</div>
        <table class="daftar">
            <thead>
                <tr>
                    <th style="width: 18%;">Kode</th>
                    <th style="width: 42%;">Nama Produk</th>
                    <th style="width: 14%;">Harga</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 16%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualan->detailPenjualans as $detail)
                    <tr>
                        <td>{{ $detail->produk->kode_produk ?? '-' }}</td>
                        <td>{{ $detail->produk->nama_produk }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $detail->qty }}</td>
                        <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL & DISKON --}}
        <table class="total-section">
            <tr>
                <td style="width: 75%; text-align:right;">Total</td>
                <td class="text-right">Rp {{ number_format($subTotalAwal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="text-align:right;">Diskon</td>
                <td class="text-right">Rp
                    {{ $isDiscountApplied ? number_format($penjualan->diskon, 0, ',', '.') : '0' }}</td>
            </tr>
            <tr>
                <td style="text-align:right;"><strong>Total Bayar</strong></td>
                <td class="text-right"><strong>
                        Rp
                        {{ $isDiscountApplied ? number_format($totalDenganDiskon, 0, ',', '.') : number_format($totalTanpaDiskon, 0, ',', '.') }}
                    </strong></td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <p class="terbilang">
            <strong>Terbilang:</strong>
            {{ ucwords(\App\Helpers\Terbilang::make($isDiscountApplied ? $totalDenganDiskon : $totalTanpaDiskon, ' Rupiah')) }}
        </p>

        {{-- CATATAN --}}
        <div class="footer-note">
            <strong>Tanda Terima</strong><br>
            Barang yang sudah dibeli tidak dapat dikembalikan/dipertukarkan.
        </div>

        {{-- TANDA TANGAN --}}
        <table class="signature">
            <tr>
                <td>(Customer)</td>
                <td></td>
                <td>Hormat Kami,</td>
            </tr>
            <tr style="height: 40px;">
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><u>{{ $penjualan->user->name ?? 'Kasir' }}</u></td>
            </tr>
        </table>
    </div>
</body>

</html>
