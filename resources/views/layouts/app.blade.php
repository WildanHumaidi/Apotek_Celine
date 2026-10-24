<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Apotek Celine')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fdf9;
        }

        .navbar {
            background-color: #2e7d32; /* Hijau gelap */
        }

        .navbar-brand, .nav-link, .dropdown-item {
            color: white !important;
        }

        .navbar-nav .nav-link.active {
            font-weight: bold;
            border-bottom: 2px solid #c8e6c9;
        }

        .footer {
            background-color: #2e7d32;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 50px;
        }

        .btn-green {
            background-color: #43a047;
            color: white;
        }

        .btn-green:hover {
            background-color: #388e3c;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #e8f5e9;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Apotek Celine</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pasien.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pasien.riwayat_pesanan') }}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-white">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="py-4">
        <div class="container">
            @include('partials.alerts') {{-- Jika ada --}}
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <small>&copy; {{ date('Y') }} Apotek Celine. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
