@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Detail Pesanan: {{ $transaksi->no_transaksi }}</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p><strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</p>
                    <p><strong>Pasien:</strong> {{ $transaksi->pasien->nama_pasien ?? 'N/A' }} ({{ $transaksi->pasien->no_telp ?? 'N/A' }})</p>
                    <p><strong>Total Pembayaran:</strong> Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</p>
                    <p><strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $transaksi->metode_pembayaran ?? 'N/A')) }}</p>
                    <p><strong>Status Pesanan:</strong>
                        <span class="badge {{
                            $transaksi->status_pesanan == 'menunggu_pembayaran' ? 'bg-warning' :
                            ($transaksi->status_pesanan == 'terbayar' ? 'bg-info' :
                            ($transaksi->status_pesanan == 'diproses' ? 'bg-primary' :
                            ($transaksi->status_pesanan == 'siap_diambil' ? 'bg-success' : // New status
                            ($transaksi->status_pesanan == 'selesai' ? 'bg-success' : 'bg-danger'))))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $transaksi->status_pesanan)) }}
                        </span>
                    </p>
                    <p><strong>Ditangani Oleh (Pembuat Transaksi):</strong> {{ $transaksi->adminUser->name ?? 'Sistem Otomatis / Pasien' }}</p>

                    <h5 class="mt-4">Detail Item Obat:</h5>
                    @if ($transaksi->detailTransaksi->isEmpty())
                        <p>Tidak ada detail obat untuk transaksi ini.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Obat</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaksi->detailTransaksi as $detail)
                                <tr>
                                    <td>{{ $detail->obat->nama_obat ?? 'Obat Tidak Ditemukan' }}</td>
                                    <td>Rp {{ number_format($detail->jumlah > 0 ? $detail->harga_bayar / $detail->jumlah : 0, 2, ',', '.') }}</td>
                                    <td>{{ $detail->jumlah }}</td>
                                    <td>Rp {{ number_format($detail->harga_bayar, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <hr class="my-4">

                    {{-- FORM UNTUK UPDATE STATUS PESANAN --}}
                    <h5>Ubah Status Pesanan:</h5>
                    <form action="{{ route('apoteker.pesanan.update_status', $transaksi->no_transaksi) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row mb-3">
                            <label for="status_pesanan" class="col-md-3 col-form-label">Status Baru</label>
                            <div class="col-md-6">
                                <select name="status_pesanan" id="status_pesanan" class="form-control @error('status_pesanan') is-invalid @enderror" required>
                                    <option value="">-- Pilih Status --</option>
                                    @foreach ($availableStatuses as $value => $label)
                                        <option value="{{ $value }}" {{ $transaksi->status_pesanan == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status_pesanan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning w-100">Update Status</button>
                            </div>
                        </div>
                    </form>

                    <a href="{{ route('apoteker.pesanan.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Pesanan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection