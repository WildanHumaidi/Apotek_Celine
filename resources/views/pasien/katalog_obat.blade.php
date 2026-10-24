@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Katalog Obat</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('pasien.keranjang.view') }}" class="btn btn-warning">
                            <i class="fas fa-shopping-cart"></i> Keranjang Belanja ({{ count(session()->get('cart', [])) }})
                        </a>
                    </div>

                    @if ($obats->isEmpty())
                        <div class="alert alert-info">Tidak ada obat yang tersedia saat ini.</div>
                    @else
                        <div class="row">
                            @foreach ($obats as $obat)
                            <div class="col-md-4 mb-4"> {{-- Menggunakan grid Bootstrap untuk card --}}
                                <div class="card h-100"> {{-- Pastikan card punya tinggi yang sama --}}
                                    {{-- Tampilan Foto Obat --}}
                                    @if($obat->foto)
                                        <img src="{{ asset('storage/' . $obat->foto) }}" class="card-img-top" alt="Foto {{ $obat->nama_obat }}" style="height: 200px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('images/default_obat.png') }}" class="card-img-top" alt="No Image" style="height: 200px; object-fit: cover;"> {{-- Pastikan punya gambar default di public/images/ --}}
                                    @endif
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $obat->nama_obat }}</h5>
                                        <p class="card-text text-muted">{{ $obat->kategori }}</p>
                                        @if($obat->deskripsi)
                                            <p class="card-text small">{{ \Illuminate\Support\Str::limit($obat->deskripsi, 100) }}</p> {{-- Tampilkan deskripsi, potong jika panjang --}}
                                        @endif
                                        <p class="card-text">Harga: <strong>Rp {{ number_format($obat->harga, 2, ',', '.') }}</strong></p>
                                        <p class="card-text">Stok: <span class="{{ $obat->stok < 10 ? 'text-danger fw-bold' : '' }}">{{ $obat->stok }}</span></p>

                                        <form action="{{ route('pasien.keranjang.add') }}" method="POST" class="mt-auto">
                                            @csrf
                                            <input type="hidden" name="id_obat" value="{{ $obat->id_obat }}">
                                            <div class="mb-2">
                                                <label for="jumlah_{{ $obat->id_obat }}" class="form-label">Jumlah:</label>
                                                <input type="number" id="jumlah_{{ $obat->id_obat }}" name="jumlah" class="form-control" value="1" min="1" max="{{ $obat->stok }}" required>
                                                @error('jumlah')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-success w-100" {{ $obat->stok == 0 ? 'disabled' : '' }}>
                                                {{ $obat->stok == 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection