@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Detail Transaksi: {{ $transaksi->no_transaksi }}</div>
                <div class="card-body">
                    <p><strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</p>
                    <p><strong>Pasien:</strong> {{ $transaksi->pasien->nama_pasien ?? 'N/A' }} ({{ $transaksi->pasien->no_telp ?? 'N/A' }})</p>
                    <p><strong>Ditangani Oleh:</strong> {{ $transaksi->adminUser->name ?? 'N/A' }}</p>
                    <p><strong>Total Pembayaran:</strong> Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</p>

                    <h5 class="mt-4">Detail Item Obat:</h5>
                    @if ($transaksi->detailTransaksi->isEmpty())
                        <p>Tidak ada detail obat untuk transaksi ini.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Obat</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaksi->detailTransaksi as $detail)
                                <tr>
                                    <td>{{ $detail->obat->nama_obat ?? 'Obat Tidak Ditemukan' }}</td>
                                    <td>Rp {{ number_format($detail->jumlah > 0 ? $detail->harga_bayar / $detail->jumlah : 0, 2, ',', '.') }}</td>
                                    <td>{{ $detail->jumlah }}</td>
                                    <td>Rp {{ number_format($detail->harga_bayar, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    <a href="{{ route('apoteker.transaksi.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Transaksi</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection