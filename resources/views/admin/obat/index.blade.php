@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-pills me-2"></i> Manajemen Data Obat
                </div>
                <div class="card-body">
                    {{-- Notifikasi Sukses --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Tombol Aksi --}}
                    <div class="mb-4 d-flex justify-content-between">
                        <a href="{{ route('admin.obat.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Tambah Obat Baru
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- Tabel Obat --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ID Obat</th>
                                    <th>Foto</th>
                                    <th>Nama Obat</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($obats as $obat)
                                    <tr class="text-center">
                                        <td>{{ $obat->id_obat }}</td>
                                        <td>
                                            @if ($obat->foto)
                                                <img src="{{ asset('storage/' . $obat->foto) }}" alt="{{ $obat->nama_obat }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">Tidak Ada Foto</span>
                                            @endif
                                        </td>
                                        <td>{{ $obat->nama_obat }}</td>
                                        <td>Rp {{ number_format($obat->harga, 2, ',', '.') }}</td>
                                        <td>{{ $obat->stok }}</td>
                                        <td>
                                            <a href="{{ route('admin.obat.edit', $obat->id_obat) }}" class="btn btn-warning btn-sm me-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.obat.destroy', $obat->id_obat) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data obat.</td>
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
