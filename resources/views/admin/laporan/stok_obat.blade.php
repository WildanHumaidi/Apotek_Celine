@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Laporan Stok Obat</div>
                <div class="card-body">
                    @if ($laporanStok->isEmpty())
                        <div class="alert alert-warning">Tidak ada data obat.</div>
                    @else
                        <table class="table table-bordered table-striped">
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
                        <a href="{{ route('admin.laporan.cetak_stok_obat_pdf') }}" class="btn btn-success mt-3">Cetak PDF</a>
                    @endif
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary mt-3 ms-2">Kembali ke Opsi Laporan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection