<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Obat</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: right; margin-top: 20px; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Stok Obat Apotek Celine</h2>
    </div>

    @if ($laporanStok->isEmpty())
        <p>Tidak ada data obat.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nama Obat</th>
                    <th>Kategori</th>
                    <th>Stok Saat Ini</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanStok as $obat)
                <tr>
                    <td>{{ $obat->nama_obat }}</td>
                    <td>{{ $obat->kategori }}</td>
                    <td>{{ $obat->stok }}</td>
                    <td>Rp {{ number_format($obat->harga, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
    </div>
</body>
</html>