<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login'); // Arahkan ke login jika belum login
        }

        $user = Auth::user();

        // Periksa apakah peran user ada dalam daftar peran yang diizinkan
        if (!$user->peran || !in_array($user->peran->nama_peran, $roles)) {
            // Jika tidak diizinkan, arahkan ke halaman 403 atau dashboard mereka
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
            // Atau redirect ke halaman default user:
            // return redirect('/home');
        }

        return $next($request);
    }
}