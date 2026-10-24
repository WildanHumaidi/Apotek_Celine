@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Obat</div>
                <div class="card-body">
                    <form action="{{ route('admin.obat.update', $obat->id_obat) }}" method="POST" enctype="multipart/form-data"> {{-- <<< PENTING: enctype --}}
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nama_obat" class="form-label">Nama Obat</label>
                            <input type="text" class="form-control @error('nama_obat') is-invalid @enderror" id="nama_obat" name="nama_obat" value="{{ old('nama_obat', $obat->nama_obat) }}" required>
                            @error('nama_obat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi">{{ old('deskripsi', $obat->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <input type="number" step="0.01" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', $obat->harga) }}" required>
                            @error('harga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <input type="text" class="form-control @error('kategori') is-invalid @enderror" id="kategori" name="kategori" value="{{ old('kategori', $obat->kategori) }}" required>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control @error('stok') is-invalid @enderror" id="stok" name="stok" value="{{ old('stok', $obat->stok) }}" required>
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Obat</label> {{-- <<< INPUT FILE --}}
                            @if($obat->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $obat->foto) }}" alt="Foto Saat Ini" style="max-width: 150px; height: auto;">
                                    <small class="text-muted"> (Foto saat ini)</small>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" name="foto" accept="image/*">
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('admin.obat.index') }}" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection