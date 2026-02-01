<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: 21.6cm 33cm;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            margin: 0.8cm 1cm;
            line-height: 1.15;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .judul {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
        }

        .no {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 6px;
        }

        .header td {
            vertical-align: top;
            font-size: 12pt;
        }

        .kiri {
            width: 55%;
        }

        .kanan {
            width: 45%;
        }

        .nama-toko {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .alamat-toko {
            font-size: 10pt;
            line-height: 1.3;
        }

        /* ===== TABEL BARANG ===== */
        .barang {
            table-layout: fixed;
            /* PENTING untuk print */
        }

        .barang th,
        .barang td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 12pt;
            word-wrap: break-word;
        }

        .barang th {
            text-align: center;
            font-weight: bold;
        }

        /* Kolom TOTAL dipastikan lega */
        .barang th:last-child,
        .barang td:last-child {
            font-size: 11pt;
        }

        .footer {
            margin-top: 6px;
            font-size: 12pt;
        }

        .footer td {
            width: 33%;
            vertical-align: top;
        }

        .note-box {
            border: 1px solid #000;
            display: inline-block;
            padding: 8px 18px;
            font-size: 10pt;
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        $page =
            $page ??
            (object) [
                'nama_toko' => 'INTI PERAGA MANDIRI',
                'jalan' => 'Jl. Jend. Ahmad Yani No.157',
                'kota' => 'Pekanbaru - Riau',
                'telepon' => '0813-7586-6604',
                'nama_pemilik' => 'Pemilik',
            ];
    @endphp

    <div class="judul">FAKTUR PENJUALAN</div>
    <div class="no">No. {{ $penjualan->kode_penjualan }}</div>

    <table class="header">
        <tr>
            <td class="kiri">
                <div class="nama-toko">{{ strtoupper($page->nama_toko) }}</div>
                <div class="alamat-toko">
                    {{ $page->jalan }}<br>
                    {{ $page->kota }}<br>
                    Telp. {{ $page->telepon }}
                </div>
            </td>
            <td class="kanan">
                Tanggal Invoice : {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y') }}<br>
                Kepada Yth : {{ $penjualan->pelanggan->nama_pelanggan ?? '-CASH-' }}
            </td>
        </tr>
    </table>

    <br>

    <table class="barang">
        <thead>
            <tr>
                <th style="width:12%">Kode</th>
                <th style="width:38%">Nama Produk</th>
                <th style="width:15%">Harga</th>
                <th style="width:8%">Qty</th>

                @if ($isDiscountApplied)
                    <th style="width:7%">Disc %</th>
                @endif

                <th style="width:20%">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($penjualan->detailPenjualans as $d)
                @php
                    $totalMurni = $d->harga_satuan * $d->qty;

                    if ($item_total_type === 'MURNI') {
                        $totalItem = $totalMurni;
                    } else {
                        $discPersen = $d->diskon_percent ?? 0;
                        $discNilai = ($discPersen / 100) * $totalMurni;
                        $totalItem = $totalMurni - $discNilai;
                    }
                @endphp
                <tr>
                    <td>{{ $d->produk->kode_produk }}</td>
                    <td>{{ $d->produk->nama_produk }}</td>
                    <td align="right">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                    <td align="center">{{ $d->qty }}</td>

                    @if ($isDiscountApplied)
                        <td align="center">{{ number_format($d->diskon_percent ?? 0, 0) }}%</td>
                    @endif

                    <td align="right">Rp {{ number_format($totalItem, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="{{ $isDiscountApplied ? 5 : 4 }}" align="right"><strong>Subtotal</strong></td>
                <td align="right"><strong>Rp {{ number_format($subTotalAwal, 0, ',', '.') }}</strong></td>
            </tr>

            @if ($isDiscountApplied && $penjualan->diskon_nominal > 0)
                <tr>
                    <td colspan="{{ $isDiscountApplied ? 5 : 4 }}" align="right">
                        Diskon Transaksi ({{ number_format($penjualan->diskon_percent ?? 0, 0) }}%)
                    </td>
                    <td align="right">- Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="{{ $isDiscountApplied ? 5 : 4 }}" align="right"><strong>TOTAL BAYAR</strong></td>
                <td align="right"><strong>Rp {{ number_format($totalFinal, 0, ',', '.') }}</strong></td>
            </tr>

            <tr>
                <td colspan="{{ $isDiscountApplied ? 6 : 5 }}" style="font-size:9pt;">
                    Terbilang : {{ ucwords(\App\Helpers\Terbilang::make($totalFinal, ' Rupiah')) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td>
                <strong>Tanda Terima</strong><br><br><br><br>
                ({{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }})
            </td>

            <td style="text-align:center;">
                <div class="note-box">
                    Barang yang sudah dibeli tidak dapat dikembalikan/ditukar
                </div>
            </td>

            <td style="text-align:right;">
                <strong>Hormat Kami,</strong><br><br><br><br>
                ({{ $page->nama_pemilik }})
            </td>
        </tr>
    </table>

</body>

</html>
