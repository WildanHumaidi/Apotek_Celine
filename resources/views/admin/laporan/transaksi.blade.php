@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Laporan Transaksi Penjualan</div>
                <div class="card-body">
                    @if ($request->filled('tanggal_mulai') || $request->filled('tanggal_akhir'))
                        <p>Periode:
                            {{ $request->tanggal_mulai ? \Carbon\Carbon::parse($request->tanggal_mulai)->format('d-m-Y') : 'Awal' }}
                            sampai
                            {{ $request->tanggal_akhir ? \Carbon\Carbon::parse($request->tanggal_akhir)->format('d-m-Y') : 'Sekarang' }}
                        </p>
                    @endif

                    @if ($laporanTransaksis->isEmpty())
                        <div class="alert alert-warning">Tidak ada transaksi dalam periode yang dipilih.</div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Pasien</th>
                                    <th>Admin</th>
                                    <th>Total Item</th>
                                    <th>Total Pembayaran</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanTransaksis as $transaksi)
                                <tr>
                                    <td>{{ $transaksi->no_transaksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                                    <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
                                    <td>{{ $transaksi->adminUser->name ?? 'N/A' }}</td>
                                    <td>{{ $transaksi->detailTransaksi->sum('jumlah') }}</td>
                                    <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('admin.transaksi_penjualan.show', $transaksi->no_transaksi) }}" class="btn btn-info btn-sm">Lihat</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('admin.laporan.cetak_transaksi_pdf', [
                            'tanggal_mulai' => $request->tanggal_mulai,
                            'tanggal_akhir' => $request->tanggal_akhir
                        ]) }}" class="btn btn-success mt-3">Cetak PDF</a>
                    @endif
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary mt-3 ms-2">Kembali ke Opsi Laporan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection