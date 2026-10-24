@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pilih Laporan Apoteker</div>
                <div class="card-body">
                    <h5 class="mb-3">Laporan Penjualan Berdasarkan Periode</h5>
                    <form action="{{ route('apoteker.laporan.penjualan_apoteker') }}" method="GET" class="mb-4"> {{-- RUTE BERUBAH --}}
                        <div class="row">
                            <div class="col-md-5">
                                <label for="tanggal_mulai" class="form-label">Dari Tanggal:</label>
                                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', \Carbon\Carbon::now()->subDays(7)->format('Y-m-d')) }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="tanggal_akhir" class="form-label">Sampai Tanggal:</label>
                                <input type="date" class="form-control @error('tanggal_akhir') is-invalid @enderror" id="tanggal_akhir" name="tanggal_akhir" value="{{ old('tanggal_akhir', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                                @error('tanggal_akhir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Lihat</button>
                            </div>
                        </div>
                    </form>

                    <hr>
                    <a href="{{ route('apoteker.dashboard') }}" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection