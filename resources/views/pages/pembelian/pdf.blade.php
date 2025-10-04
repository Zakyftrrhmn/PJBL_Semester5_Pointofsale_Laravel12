<!DOCTYPE html>
<html>

<head>
    <title>Faktur Pembelian - {{ $pembelian->kode_pembelian }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .summary {
            margin-top: 20px;
            width: 300px;
            float: right;
        }

        .summary div {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }

        .summary .total {
            font-weight: bold;
            font-size: 11px;
            border-top: 2px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Faktur Pembelian</h2>
        <p>Tanggal: {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d F Y') }}</p>
        <p>Kode Transaksi: **{{ $pembelian->kode_pembelian }}**</p>
    </div>

    <div class="info-box">
        <div>
            <p><strong>Pemasok:</strong></p>
            <p>{{ $pembelian->pemasok->nama_pemasok }}</p>
            <p>Telp: {{ $pembelian->pemasok->telp }}</p>
            <p>Alamat: {{ $pembelian->pemasok->alamat }}</p>
        </div>
        {{-- Anda bisa menambahkan informasi lain di sini --}}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Harga Beli</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembelian->detailPembelians as $no => $detail)
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ $detail->produk->kode_produk }}</td>
                    <td>{{ $detail->produk->nama_produk }}</td>
                    <td>Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ $detail->produk->satuan->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
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
        <div class="total">
            <span>TOTAL BAYAR:</span>
            <span>Rp {{ number_format($pembelian->total_bayar, 0, ',', '.') }}</span>
        </div>
    </div>
</body>

</html>
