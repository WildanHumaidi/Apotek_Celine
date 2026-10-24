@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tambah Pengguna Baru</div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">No. Telepon (Opsional)</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="id_peran" class="form-label">Peran</label>
                            <select class="form-control @error('id_peran') is-invalid @enderror" id="id_peran" name="id_peran" required>
                                <option value="">Pilih Peran</option>
                                @foreach ($perans as $peran)
                                    <option value="{{ $peran->id }}" {{ old('id_peran') == $peran->id ? 'selected' : '' }}>
                                        {{ ucfirst($peran->nama_peran) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_peran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection