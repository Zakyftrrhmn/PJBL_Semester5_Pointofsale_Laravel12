<!DOCTYPE html>
<html>

<head>
    <title>Faktur Pembelian - {{ $pembelian->kode_pembelian }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 40px;
        }

        /* === JUDUL === */
        .title-section {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 25px;
        }

        /* === INFO TRANSAKSI === */
        .info-box {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-box div {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .info-box p {
            margin: 2px 0;
            font-size: 9pt;
        }

        /* === TABEL DETAIL === */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #dbe4f0;
            text-align: center;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* === RINGKASAN TOTAL === */
        .summary {
            margin-top: 25px;
            width: 300px;
            float: right;
            font-size: 9pt;
        }

        .summary div {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dashed #aaa;
        }

        .summary .total {
            font-weight: bold;
            font-size: 10pt;
            border-top: 2px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    {{-- === JUDUL FAKTUR === --}}
    <div class="title-section">FAKTUR PEMBELIAN</div>

    {{-- === INFO TRANSAKSI === --}}
    <div class="info-box">
        <div>
            <p><strong>Kode Transaksi:</strong> {{ $pembelian->kode_pembelian }}</p>
            <p><strong>Tanggal:</strong>
                {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->translatedFormat('d F Y') }}</p>
        </div>
        <div>
            <p><strong>Pemasok:</strong> {{ $pembelian->pemasok->nama_pemasok }}</p>
            <p><strong>Telp:</strong> {{ $pembelian->pemasok->telp ?? '-' }}</p>
            <p><strong>Alamat:</strong> {{ $pembelian->pemasok->alamat ?? '-' }}</p>
        </div>
    </div>

    {{-- === TABEL DETAIL PRODUK === --}}
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Produk</th>
                <th width="25%">Nama Produk</th>
                <th width="12%">Harga Beli</th>
                <th width="10%">Jumlah</th>
                <th width="10%">Satuan</th>
                <th width="15%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembelian->detailPembelians as $no => $detail)
                <tr>
                    <td class="text-center">{{ $no + 1 }}</td>
                    <td>{{ $detail->produk->kode_produk }}</td>
                    <td>{{ $detail->produk->nama_produk }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $detail->jumlah }}</td>
                    <td class="text-center">{{ $detail->produk->satuan->nama_satuan ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div>
            <span>Total Harga Bruto:</span>
            <span>Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</span>
        </div>
        <div>
            <span>Diskon:</span>
            <span>- Rp {{ number_format($pembelian->diskon, 0, ',', '.') }}</span>
        </div>
        <div>
            <span>PPN:</span>
            <span>+ Rp {{ number_format($pembelian->ppn, 0, ',', '.') }}</span>
        </div>

        {{-- Total Bayar Awal --}}
        <div style="border-top: 1px dashed #aaa; padding-top: 5px;">
            <span>Total Bayar Awal:</span>
            <span>Rp {{ number_format($pembelian->total_bayar, 0, ',', '.') }}</span>
        </div>

        {{-- Retur --}}
        @if ($pembelian->total_nilai_retur > 0)
            <div style="color: red;">
                <span>Total Nilai Retur:</span>
                <span>- Rp {{ number_format($pembelian->total_nilai_retur, 0, ',', '.') }}</span>
            </div>
        @endif

        {{-- Total Bayar Akhir --}}
        <div class="total">
            <span>SISA TOTAL BAYAR:</span>
            <span>Rp {{ number_format($pembelian->sisa_total_bayar, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="clear"></div>
</body>

</html>
