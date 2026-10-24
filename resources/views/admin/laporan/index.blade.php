@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-alt me-2"></i> Pilih Jenis Laporan
                </div>
                <div class="card-body">
                    {{-- Laporan Transaksi --}}
                    <h4 class="card-title mb-3"><i class="fas fa-receipt me-2"></i> Laporan Transaksi Penjualan</h4>
                    <p class="card-text mb-3">Filter laporan transaksi berdasarkan rentang tanggal tertentu.</p>
                    
                    <form action="{{ route('admin.laporan.transaksi') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="tanggal_mulai" class="form-label">Dari Tanggal:</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                @error('tanggal_mulai')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="tanggal_akhir" class="form-label">Sampai Tanggal:</label>
                                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                                @error('tanggal_akhir')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Lihat
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    {{-- Laporan Stok Obat --}}
                    <h4 class="card-title mb-3"><i class="fas fa-capsules me-2"></i> Laporan Stok Obat</h4>
                    <p class="card-text mb-3">Lihat daftar lengkap stok obat yang tersedia saat ini.</p>
                    <a href="{{ route('admin.laporan.stok_obat') }}" class="btn btn-info">
                        <i class="fas fa-eye me-1"></i> Lihat Laporan Stok
                    </a>

                    <hr class="my-4">

                    {{-- Tombol Kembali --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
