@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0">Login</h5>
                </div>

                <div class="card-body">
                    {{-- Form Login --}}
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                id="email" 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autocomplete="email" 
                                autofocus>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                name="password" 
                                required 
                                autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="mb-3 form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="remember" 
                                id="remember" 
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>

                        {{-- Tombol Login --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                Login
                            </button>
                        </div>

                        {{-- Link Lupa Password --}}
                        <div class="text-center mt-3">
                            @if (Route::has('password.request'))
                                <a class="text-decoration-none" href="{{ route('password.request') }}">
                                    Lupa Password?
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Garis Pemisah --}}
                    <hr class="my-4">

                    {{-- Login Google --}}
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('auth.google') }}" class="btn btn-danger">
                            <i class="fab fa-google me-2"></i> Login dengan Google
                        </a>
                    </div>

                    {{-- Tombol Registrasi --}}
                    @if (Route::has('register'))
                        <div class="text-center">
                            <p class="mb-2">Belum punya akun?</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                Registrasi Sekarang
                            </a>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if (session('error'))
                        <div class="alert alert-danger text-center mt-3">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
