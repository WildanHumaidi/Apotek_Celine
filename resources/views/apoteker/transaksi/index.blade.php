@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Transaksi Penjualan Saya</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="mb-3">
                        <a href="{{ route('apoteker.transaksi.resep.create') }}" class="btn btn-primary">Transaksi Dengan Resep</a>
                        <a href="{{ route('apoteker.transaksi.tanpa_resep.create') }}" class="btn btn-info ms-2">Transaksi Tanpa Resep</a>
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Nama Pasien</th>
                                <th>Total Item</th>
                                <th>Total Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksis as $transaksi)
                            <tr>
                                <td>{{ $transaksi->no_transaksi }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                                <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
                                <td>{{ $transaksi->detailTransaksi->sum('jumlah') }}</td>
                                <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('apoteker.transaksi.show', $transaksi->no_transaksi) }}" class="btn btn-info btn-sm">Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($transaksis->isEmpty())
                        <p class="text-center">Anda belum membuat transaksi apapun.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection