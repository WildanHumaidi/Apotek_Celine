@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Keranjang Belanja Anda</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (empty($cart))
                        <div class="alert alert-info">Keranjang belanja Anda kosong. <a href="{{ route('pasien.katalog_obat') }}">Mulai belanja sekarang!</a></div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nama Obat</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
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
                                        <td>
                                            <form action="{{ route('pasien.keranjang.update') }}" method="POST" class="d-flex">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="id_obat" value="{{ $item['id_obat'] }}">
                                                <input type="number" name="jumlah" value="{{ $item['jumlah'] }}" min="0" class="form-control w-50">
                                                <button type="submit" class="btn btn-sm btn-secondary ms-2">Update</button>
                                            </form>
                                        </td>
                                        <td>Rp {{ number_format($subtotal, 2, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('pasien.keranjang.remove') }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id_obat" value="{{ $item['id_obat'] }}">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini dari keranjang?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Belanja:</strong></td>
                                    <td colspan="2"><strong>Rp {{ number_format($grandTotal, 2, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('pasien.katalog_obat') }}" class="btn btn-secondary">Lanjutkan Belanja</a>
                            <a href="{{ route('pasien.checkout.show') }}" class="btn btn-success">Proses ke Checkout</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection