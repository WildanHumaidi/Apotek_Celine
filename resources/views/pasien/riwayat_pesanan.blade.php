@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Riwayat Pesanan Anda</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Bagian Baru: Instruksi Pembayaran --}}
                    @if (session('last_order_no'))
                        @php
                            $lastOrder = \App\Models\TransaksiPenjualan::where('no_transaksi', session('last_order_no'))
                                ->where('id_pasien', Auth::user()->id)
                                ->first();
                        @endphp
                        @if ($lastOrder && $lastOrder->metode_pembayaran == 'transfer_bank_ambil_apotek' && $lastOrder->status_pesanan == 'menunggu_pembayaran')
                            <div class="alert alert-info border border-info">
                                <h5>Pesanan Anda Dengan No. Transaksi: <strong>{{ $lastOrder->no_transaksi }}</strong> Berhasil Dibuat!</h5>
                                <p>Silakan lakukan pembayaran sejumlah <strong>Rp {{ number_format($lastOrder->harga_bayar, 2, ',', '.') }}</strong> melalui transfer bank ke rekening berikut:</p>
                                <ul>
                                    <li>**Bank BCA**</li>
                                    <li>**No. Rekening: 123-456-7890**</li>
                                    <li>**Atas Nama: Apotek Celine**</li>
                                </ul>
                                <p>Setelah transfer, pesanan Anda akan diverifikasi oleh Apoteker. Anda dapat mengambil pesanan di Apotek kami setelah status pesanan berubah menjadi 'Terbayar' atau 'Siap Diambil'.</p>
                                <p>Status pesanan Anda saat ini: <strong>{{ ucfirst(str_replace('_', ' ', $lastOrder->status_pesanan)) }}</strong></p>
                                <p class="small text-muted">Mohon simpan nomor transaksi Anda.</p>
                            </div>
                        @endif
                    @endif
                    {{-- Akhir Bagian Baru --}}


                    @if ($riwayat->isEmpty())
                        <div class="alert alert-info">Anda belum memiliki riwayat pesanan.</div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Metode Pembayaran</th> {{-- TAMBAH INI --}}
                                    <th>Status Pesanan</th>      {{-- TAMBAH INI --}}
                                    <th>Total Item</th>
                                    <th>Total Bayar</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayat as $transaksi)
                                <tr>
                                    <td>{{ $transaksi->no_transaksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaksi->metode_pembayaran)) }}</td> {{-- Tampilkan metode pembayaran --}}
                                    <td>
                                        <span class="badge {{
                                            $transaksi->status_pesanan == 'menunggu_pembayaran' ? 'bg-warning' :
                                            ($transaksi->status_pesanan == 'terbayar' ? 'bg-info' :
                                            ($transaksi->status_pesanan == 'diproses' ? 'bg-primary' :
                                            ($transaksi->status_pesanan == 'selesai' ? 'bg-success' : 'bg-secondary')))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaksi->status_pesanan)) }}
                                        </span>
                                    </td> {{-- Tampilkan status pesanan --}}
                                    <td>{{ $transaksi->detailTransaksi->sum('jumlah') }}</td>
                                    <td>Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $transaksi->no_transaksi }}">
                                            Lihat Detail
                                        </button>

                                        <div class="modal fade" id="detailModal{{ $transaksi->no_transaksi }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $transaksi->no_transaksi }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel{{ $transaksi->no_transaksi }}">Detail Transaksi: {{ $transaksi->no_transaksi }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d-m-Y') }}</p>
                                                        <p><strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $transaksi->metode_pembayaran)) }}</p> {{-- Tambah di modal --}}
                                                        <p><strong>Status Pesanan:</strong> <span class="badge {{
                                                            $transaksi->status_pesanan == 'menunggu_pembayaran' ? 'bg-warning' :
                                                            ($transaksi->status_pesanan == 'terbayar' ? 'bg-info' :
                                                            ($transaksi->status_pesanan == 'diproses' ? 'bg-primary' :
                                                            ($transaksi->status_pesanan == 'selesai' ? 'bg-success' : 'bg-secondary')))
                                                        }}">
                                                            {{ ucfirst(str_replace('_', ' ', $transaksi->status_pesanan)) }}
                                                        </span></p> {{-- Tambah di modal --}}
                                                        <p><strong>Total Pembayaran:</strong> Rp {{ number_format($transaksi->harga_bayar, 2, ',', '.') }}</p>
                                                        <h6 class="mt-3">Item Obat:</h6>
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
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    <a href="{{ route('pasien.dashboard') }}" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection