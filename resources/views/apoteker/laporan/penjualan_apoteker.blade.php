@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Laporan Penjualan Apoteker</div>
                <div class="card-body">
                    <p>Periode:
                        <strong>{{ \Carbon\Carbon::parse($request->tanggal_mulai)->format('d-m-Y') }}</strong>
                        sampai
                        <strong>{{ \Carbon\Carbon::parse($request->tanggal_akhir)->format('d-m-Y') }}</strong>
                    </p>
                    <p>Total Penjualan periode ini: <strong>Rp {{ number_format($totalPenjualan, 2, ',', '.') }}</strong></p>

                    @if ($laporanPenjualan->isEmpty())
                        <div class="alert alert-warning">Tidak ada transaksi dalam periode yang dipilih.</div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Pasien</th>
                                    <th>Total Item</th>
                                    <th>Total Bayar</th>
                                    <th>Aksi</th>
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
                                    <td>
                                        <a href="{{ route('apoteker.transaksi.show', $transaksi->no_transaksi) }}" class="btn btn-info btn-sm">Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Tombol Cetak PDF --}}
                        <a href="{{ route('apoteker.laporan.cetak_penjualan_apoteker_pdf', [
                            'tanggal_mulai' => $request->tanggal_mulai,
                            'tanggal_akhir' => $request->tanggal_akhir
                        ]) }}" class="btn btn-success mt-3">Cetak PDF</a> {{-- RUTE BERUBAH --}}
                    @endif
                    <a href="{{ route('apoteker.laporan.index') }}" class="btn btn-secondary mt-3 ms-2">Kembali ke Opsi Laporan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection