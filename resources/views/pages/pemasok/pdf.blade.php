<!DOCTYPE html>
<html>

<head>
    <title>Data Pemasok</title>
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
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <h2>Daftar PEmasok</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pemasoks as $no => $p)
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ $p->nama_pemasok }}</td>
                    <td>{{ $p->telp }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->alamat }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
