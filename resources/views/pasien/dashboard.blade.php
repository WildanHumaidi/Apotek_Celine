@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('content')
<div class="container py-4">
    <!-- Header Selamat Datang -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-success text-white p-4 rounded shadow-sm d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Halo, {{ Auth::user()->name }} </h3>
                    <p class="mb-0">Selamat datang di Dashboard Pasien <strong>Apotek Celine</strong>. Anda dapat mencari obat, membuat pesanan, dan melihat riwayat pembelian Anda.</p>
                </div>
                {{-- TOMBOL KERANJANG BELANJA --}}
                <a href="{{ route('pasien.keranjang.view') }}" class="btn btn-warning text-dark btn-lg d-flex align-items-center">
                    <i class="fas fa-shopping-cart me-2"></i> Keranjang
                    <span class="badge bg-danger ms-2">{{ count(session()->get('cart', [])) }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigasi Utama -->
    <div class="row g-4 mb-5">
        {{-- Katalog Obat --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 text-center">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-pills fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">Katalog Obat</h5>
                    <p class="text-muted small">Lihat daftar obat yang tersedia dan pilih sesuai kebutuhan.</p>
                    <a href="{{ route('pasien.katalog_obat') }}" class="btn btn-outline-primary btn-sm">Lihat Katalog</a>
                </div>
            </div>
        </div>

        {{-- Riwayat Pesanan --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 text-center">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title">Riwayat Pesanan</h5>
                    <p class="text-muted small">Pantau pesanan dan riwayat transaksi Anda sebelumnya.</p>
                    <a href="{{ route('pasien.riwayat_pesanan') }}" class="btn btn-outline-info btn-sm">Lihat Riwayat</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Katalog Obat Unggulan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom fw-bold">
            Katalog Obat Unggulan
        </div>
        <div class="card-body">
            @isset($featuredObats)
                @if($featuredObats->isNotEmpty())
                    <div class="row g-4">
                        @foreach ($featuredObats as $obat)
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    {{-- Gambar Obat --}}
                                    <img src="{{ $obat->foto ? asset('storage/' . $obat->foto) : asset('images/default_obat.png') }}"
                                         class="card-img-top" alt="Foto {{ $obat->nama_obat }}"
                                         style="height: 160px; object-fit: cover;">

                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title">{{ $obat->nama_obat }}</h6>
                                        <p class="card-text text-muted small">{{ $obat->kategori }}</p>
                                        @if($obat->deskripsi)
                                            <p class="small">{{ \Illuminate\Support\Str::limit($obat->deskripsi, 60) }}</p>
                                        @endif
                                        <p class="fw-bold text-success">Rp {{ number_format($obat->harga, 2, ',', '.') }}</p>
                                        <p class="small">Stok: {{ $obat->stok }}</p>

                                        {{-- Form Tambah ke Keranjang --}}
                                        <form action="{{ route('pasien.keranjang.add') }}" method="POST" class="mt-auto">
                                            @csrf
                                            <input type="hidden" name="id_obat" value="{{ $obat->id_obat }}">
                                            <div class="mb-2">
                                                <input type="number" name="jumlah" class="form-control form-control-sm"
                                                       value="1" min="1" max="{{ $obat->stok }}" required>
                                            </div>
                                            <button type="submit"
                                                    class="btn btn-sm btn-success w-100 {{ $obat->stok == 0 ? 'disabled' : '' }}">
                                                {{ $obat->stok == 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('pasien.katalog_obat') }}" class="btn btn-outline-primary">Lihat Semua Obat</a>
                    </div>
                @else
                    <div class="alert alert-info text-center mb-0">Belum ada produk unggulan yang ditampilkan.</div>
                @endif
            @else
                <div class="alert alert-warning text-center mb-0">Data obat belum tersedia. Silakan coba lagi nanti.</div>
            @endisset
        </div>
    </div>
</div>
@endsection