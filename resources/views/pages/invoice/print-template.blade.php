<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $penjualan->kode_penjualan }}</title>

    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1px 0;
        }

        .item-name {
            width: 60%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="text-center">
            {{-- Ganti dengan identitas toko --}}
            <strong>INTI PERAGA MANDIRI</strong><br>
            <span style="font-size: 9px;">Jl. Contoh No. 123, Kota Anda</span><br>
            <span style="font-size: 9px;">Telp: 0812-XXXX-XXXX</span>
        </div>

        <div class="divider"></div>

        <table>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td>{{ $penjualan->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>:</td>
                <td>{{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($penjualan->created_at)->format('d/m/y H:i') }}</td>
            </tr>
            <tr>
                <td>No. Trx</td>
                <td>:</td>
                <td>{{ $penjualan->kode_penjualan }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- ITEM PENJUALAN --}}
        <table>
            @foreach ($penjualan->detailPenjualans as $detail)
                <tr>
                    <td colspan="3" class="item-name">{{ $detail->produk->nama_produk ?? 'Produk Dihapus' }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 10px;">
                        {{ number_format($detail->qty, 0, ',', '.') }} x
                        {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td class="text-right" colspan="2">
                        {{ number_format($detail->qty * $detail->harga_satuan, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="divider"></div>

        {{-- BAGIAN TOTAL --}}
        <table>
            @if ($isDiscountApplied)
                {{-- === DENGAN DISKON === --}}
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ number_format($subTotalAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td class="text-right">({{ number_format($penjualan->diskon, 0, ',', '.') }})</td>
                </tr>
                <tr>
                    <td>Total (Net)</td>
                    <td class="text-right"><strong>{{ number_format($totalDenganDiskon, 0, ',', '.') }}</strong></td>
                </tr>
            @else
                {{-- === TANPA DISKON === --}}
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ number_format($subTotalAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total (Net)</td>
                    <td class="text-right"><strong>{{ number_format($totalTanpaDiskon, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        {{-- BAGIAN PEMBAYARAN --}}
        <table>
            <tr>
                <td>Bayar</td>
                <td class="text-right">{{ number_format($bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="text-right">{{ number_format($kembalian, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="text-center" style="margin-top: 5px;">
            <p style="margin: 2px 0;">Terima Kasih atas kunjungan Anda!</p>
            <p style="margin: 2px 0; font-size: 8px;">Barang yang sudah dibeli tidak dapat dikembalikan.</p>
        </div>
    </div>

    <script>
        // Cetak otomatis
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
