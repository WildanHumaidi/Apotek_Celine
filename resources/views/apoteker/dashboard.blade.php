@extends('layouts.app')

@section('title', 'Dashboard Apoteker | Apotek Celine')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Header --}}
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-primary text-white fw-bold rounded-top-3">
                    <i class="fas fa-user-nurse me-2"></i> Selamat Datang, Apoteker!
                </div>
                <div class="card-body">
                    <h5 class="fw-semibold">Halo, <span class="text-primary">{{ Auth::user()->name }}</span> 👋</h5>
                    <p class="text-muted">Selamat datang di <strong>Dashboard Apoteker</strong> sistem <strong>Apotek Celine</strong>. Anda dapat mengelola transaksi penjualan obat, memantau pesanan pasien, serta membuat laporan.</p>
                </div>
            </div>

            {{-- Card Menu --}}
            <div class="row row-cols-1 row-cols-md-3 g-4">
                {{-- Transaksi Baru --}}
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-header bg-success text-white fw-semibold rounded-top-4">
                            <i class="fas fa-cash-register me-2"></i> Buat Transaksi Baru
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="mb-3 text-muted">Mulai transaksi penjualan obat, baik dengan resep maupun tanpa resep.</p>
                            <a href="{{ route('apoteker.transaksi.index') }}" class="btn btn-success mt-auto w-100 rounded-pill">
                                <i class="fas fa-plus-circle me-1"></i> Mulai Transaksi
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Manajemen Pesanan --}}
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-header bg-info text-white fw-semibold rounded-top-4">
                            <i class="fas fa-clipboard-check me-2"></i> Manajemen Pesanan Pasien
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="mb-3 text-muted">Lihat dan perbarui status pesanan obat dari pasien.</p>
                            <a href="{{ route('apoteker.pesanan.index') }}" class="btn btn-info mt-auto w-100 rounded-pill">
                                <i class="fas fa-tasks me-1"></i> Kelola Pesanan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Laporan Penjualan --}}
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-header bg-secondary text-white fw-semibold rounded-top-4">
                            <i class="fas fa-file-alt me-2"></i> Laporan Penjualan
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="mb-3 text-muted">Lihat dan cetak laporan transaksi yang telah Anda proses.</p>
                            <a href="{{ route('apoteker.laporan.index') }}" class="btn btn-secondary mt-auto w-100 rounded-pill">
                                <i class="fas fa-chart-line me-1"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
