<!DOCTYPE html>
<html>

<head>
    <title>Data Produk</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <h2>Daftar Produk</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Merek</th>
                <th>Satuan</th>
                <th>Stok</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Status</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produks as $no => $produk)
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ $produk->kode_produk }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
                    <td>{{ $produk->merek->nama_merek ?? '-' }}</td>
                    <td>{{ $produk->satuan->nama_satuan ?? '-' }}</td>
                    <td>{{ $produk->stok_produk }}</td>
                    <td>Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($produk->is_active) }}</td>
                    <td>{{ $produk->deskripsi_produk ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
