<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan Apoteker</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: right; margin-top: 20px; font-size: 8px; }
        .total-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan Apoteker</h2>
        <p>Periode:
            {{ \Carbon\Carbon::parse($request->tanggal_mulai)->format('d-m-Y') }}
            sampai
            {{ \Carbon\Carbon::parse($request->tanggal_akhir)->format('d-m-Y') }}
        </p>
        <p>Apoteker: {{ Auth::user()->name }}</p>
    </div>

    @if ($laporanPenjualan->isEmpty())
        <p>Tidak ada transaksi dalam periode yang dipilih.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Total Item</th>
                    <th>Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanPenjualan as $transaksi)
                <tr>
                    <td>{{ $transaksi->no_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                    <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
                    <td>{{ $transaksi->detailTransaksi->sum('jumlah') }}</td>
                    <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Total Penjualan Periode Ini:</td>
                    <td>Rp {{ number_format($totalPenjualan, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
    </div>
</body>
</html>