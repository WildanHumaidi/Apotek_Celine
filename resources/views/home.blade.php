@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Selamat Datang</div>

                <div class="card-body text-center">
                    <p class="mb-4">Silakan login untuk melanjutkan ke sistem Apotek Celine.</p>

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                    @else
                        <a href="{{ url('/home') }}" class="btn btn-success">Ke Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
