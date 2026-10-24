@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-cash-register me-2"></i> Manajemen Transaksi Penjualan
                </div>
                <div class="card-body">
                    
                    {{-- Tombol Kembali --}}
                    <div class="mb-4 d-flex justify-content-end">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- Tabel Transaksi --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Nama Pasien</th>
                                    <th>Admin/Apoteker</th>
                                    <th>Total Bayar</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Status Pesanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaksis as $transaksi)
                                    <tr class="text-center">
                                        <td>{{ $transaksi->no_transaksi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                                        <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
                                        <td>{{ $transaksi->adminUser->name ?? 'N/A' }}</td>
                                        <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $transaksi->metode_pembayaran ?? 'N/A')) }}</td>
                                        <td>
                                            @php
                                                $status = $transaksi->status_pesanan;
                                                $statusBadge = match($status) {
                                                    'menunggu_pembayaran' => 'bg-warning',
                                                    'terbayar' => 'bg-info',
                                                    'diproses' => 'bg-primary',
                                                    'siap_diambil' => 'bg-secondary',
                                                    'selesai' => 'bg-success',
                                                    default => 'bg-danger'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }}">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.transaksi_penjualan.show', $transaksi->no_transaksi) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada data transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- /table-responsive --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
