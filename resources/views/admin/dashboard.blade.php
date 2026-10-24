@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-4">
    <!-- Header Selamat Datang -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-success text-white p-4 rounded shadow-sm">
                <h3 class="mb-1">Halo, {{ Auth::user()->name }} </h3>
                <p class="mb-0">Selamat datang di Dashboard Administrator <strong>Apotek Celine</strong>. Silakan kelola data sistem dengan efisien.</p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Manajemen -->
    <div class="row g-4">
        {{-- Manajemen Obat --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-pills fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title">Manajemen Obat</h5>
                    <p class="text-muted small">Kelola stok dan data obat di sistem.</p>
                    <a href="{{ route('admin.obat.index') }}" class="btn btn-outline-success btn-sm">Lihat Obat</a>
                </div>
            </div>
        </div>

        {{-- Transaksi Penjualan --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-cash-register fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">Transaksi</h5>
                    <p class="text-muted small">Pantau riwayat penjualan obat.</p>
                    <a href="{{ route('admin.transaksi_penjualan.index') }}" class="btn btn-outline-primary btn-sm">Lihat Transaksi</a>
                </div>
            </div>
        </div>

        {{-- Laporan --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title">Laporan</h5>
                    <p class="text-muted small">Analisis dan laporan kegiatan apotek.</p>
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-success btn-sm">Lihat Laporan</a>
                </div>
            </div>
        </div>

        {{-- Pengguna --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-users-cog fa-2x text-dark"></i>
                    </div>
                    <h5 class="card-title">Pengguna</h5>
                    <p class="text-muted small">Kelola akun pengguna dan peran.</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark btn-sm">Kelola Pengguna</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
