@extends('layouts.app')

@section('title', 'Manajemen Pesanan | Apotek Celine')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="fas fa-clipboard-list me-2"></i> Manajemen Pesanan Pasien
                </div>

                <div class="card-body">
                    {{-- Flash Messages --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Kembali --}}
                    <div class="mb-3 text-end">
                        <a href="{{ route('apoteker.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- Tabel Pesanan --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Nama Pasien</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Status Pesanan</th>
                                    <th>Total Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaksis as $transaksi)
                                    <tr>
                                        <td>{{ $transaksi->no_transaksi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                                        <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $transaksi->metode_pembayaran ?? 'N/A')) }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $transaksi->status_pesanan == 'menunggu_pembayaran' ? 'bg-warning' : '' }}
                                                {{ $transaksi->status_pesanan == 'terbayar' ? 'bg-info' : '' }}
                                                {{ $transaksi->status_pesanan == 'diproses' ? 'bg-primary' : '' }}
                                                {{ $transaksi->status_pesanan == 'siap_diambil' || $transaksi->status_pesanan == 'selesai' ? 'bg-success' : '' }}
                                                {{ $transaksi->status_pesanan == 'dibatalkan' ? 'bg-danger' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $transaksi->status_pesanan)) }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                                        <td>
                                            <a href="{{ route('apoteker.pesanan.show', $transaksi->no_transaksi) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted">Belum ada pesanan masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- end table responsive --}}
                </div> {{-- end card body --}}
            </div>
        </div>
    </div>
</div>
@endsection
