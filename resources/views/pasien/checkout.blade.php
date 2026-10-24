@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Konfirmasi Pesanan & Pembayaran</div>
                <div class="card-body">

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <h5 class="mb-3">Detail Pesanan Anda:</h5>
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
                            @php $grandTotal = 0; @endphp
                            @foreach ($cart as $id => $item)
                                @php
                                    $subtotal = $item['harga'] * $item['jumlah'];
                                    $grandTotal += $subtotal;
                                @endphp
                                <tr>
                                    <td>{{ $item['nama_obat'] }}</td>
                                    <td>Rp {{ number_format($item['harga'], 2, ',', '.') }}</td>
                                    <td>{{ $item['jumlah'] }}</td>
                                    <td>Rp {{ number_format($subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total Pembayaran:</strong></td>
                                <td><strong>Rp {{ number_format($grandTotal, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <h5 class="mb-3 mt-4">Data Pasien (Informasi Anda):</h5>
                    @if ($pasien)
                        <p><strong>Nama:</strong> {{ $pasien->nama_pasien }}</p>
                        <p><strong>Alamat:</strong> {{ $pasien->alamat }}</p>
                        <p><strong>No. Telepon:</strong> {{ $pasien->no_telp }}</p>
                    @else
                        <div class="alert alert-warning">
                            Data pasien tidak ditemukan. Pastikan Anda terdaftar di tabel <code>pasien</code> dengan <code>id_pasien</code> yang sama dengan ID user Anda.
                        </div>
                    @endif

                    {{-- FORM DIMULAI DI SINI --}}
                    <form action="{{ route('pasien.checkout.process') }}" method="POST" class="mt-4">
                        @csrf

                        <h5 class="mb-3">Pilih Metode Pembayaran:</h5>
                        <div class="mb-3 @error('metode_pembayaran') is-invalid @enderror">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="transferBank" value="transfer_bank_ambil_apotek" checked required>
                                <label class="form-check-label" for="transferBank">
                                    Transfer Bank (Ambil di Apotek)
                                </label>
                            </div>

                            <div id="bank-details" class="mt-2 p-3 border rounded">
                                <h6>Detail Rekening:</h6>
                                <ul>
                                    <li><strong>Bank BCA</strong></li>
                                    <li><strong>No. Rekening:</strong> 123-456-7890</li>
                                    <li><strong>Atas Nama:</strong> Apotek Celine</li>
                                </ul>
                                <p class="small text-muted">
                                    Total yang harus dibayarkan:
                                    <strong>Rp {{ number_format($grandTotal, 2, ',', '.') }}</strong>
                                </p>
                            </div>

                            @error('metode_pembayaran')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="confirmPayment" name="confirmPayment" required>
                            <label class="form-check-label" for="confirmPayment">
                                Saya mengkonfirmasi pesanan ini dan setuju untuk melakukan pembayaran.
                            </label>
                            @error('confirmPayment')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Konfirmasi Pesanan</button>
                        <a href="{{ route('pasien.keranjang.view') }}" class="btn btn-secondary ms-2">Kembali ke Keranjang</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT OPSIONAL --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const transferBankRadio = document.getElementById('transferBank');
        const bankDetailsDiv = document.getElementById('bank-details');

        function toggleBankDetails() {
            if (transferBankRadio.checked) {
                bankDetailsDiv.style.display = 'block';
            } else {
                bankDetailsDiv.style.display = 'none';
            }
        }

        toggleBankDetails();

        document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
            radio.addEventListener('change', toggleBankDetails);
        });
    });
</script>
@endsection
