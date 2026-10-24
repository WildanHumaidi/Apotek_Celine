@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-2"></i> Manajemen Pengguna
                </div>
                <div class="card-body">

                    {{-- Flash Message --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Tombol Aksi --}}
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Tambah Pengguna Baru
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    {{-- Tabel Data Pengguna --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. Telepon</th>
                                    <th>Peran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="text-center">
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone_number ?? '-' }}</td>
                                        <td>{{ $user->peran->nama_peran ?? 'Tidak Ada Peran' }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada pengguna yang terdaftar.</td>
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
