<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Peran; // Import model Peran
use App\Models\Pasien; // Import model Pasien
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Untuk debugging jika perlu
use Illuminate\Support\Facades\Hash; // TAMBAHKAN INI
use Illuminate\Support\Str;          // TAMBAHKAN INI

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            // Dapatkan informasi user dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cari user di database berdasarkan email Google
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Jika user sudah ada, update info dan login
                $user->update([
                    'name' => $googleUser->getName(),
                    // Anda bisa update kolom lain jika perlu, misal 'avatar'
                ]);
            } else {
                // Jika user belum ada, buat user baru
                // Secara default, user yang daftar via Google akan diberi peran 'pasien'
                $pasienRole = Peran::where('nama_peran', 'pasien')->first();

                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    // Socialite tidak memberikan password, jadi bisa diisi null/random/default
                    'password' => Hash::make(Str::random(24)), // Generate random password
                    'email_verified_at' => now(),
                    'id_peran' => $pasienRole ? $pasienRole->id : null, // Set peran pasien
                ]);

                if ($pasienRole && $user->id_peran === $pasienRole->id) {
                    Pasien::firstOrCreate(
                        ['id_pasien' => $user->id],
                        [
                            'nama_pasien' => $user->name,
                            'alamat' => '-',
                            'no_telp' => '-',
                        ]
                    );
                }
            }

            // Pastikan relasi pasien ada jika user adalah pasien
            if ($user->isPasien()) {
                Pasien::firstOrCreate(
                    ['id_pasien' => $user->id],
                    [
                        'nama_pasien' => $user->name,
                        'alamat' => '-',
                        'no_telp' => '-',
                    ]
                );
            }

            // Login user
            Auth::login($user);

            // Redirect ke dashboard sesuai peran setelah login
            $redirectTo = '/home'; // Default, akan di-override oleh LoginController's redirectTo()
            if ($user->isAdmin()) {
                $redirectTo = '/admin/dashboard';
            } elseif ($user->isApoteker()) {
                $redirectTo = '/apoteker/dashboard';
            } elseif ($user->isPasien()) {
                $redirectTo = '/pasien/dashboard';
            }

            return redirect()->intended($redirectTo);

        } catch (\Exception $e) {
            Log::error('Gagal login via Google: ' . $e->getMessage());
            // Redirect ke halaman login dengan pesan error
            return redirect('/login')->with('error', 'Gagal login melalui Google. Silakan coba lagi.');
        }
    }
}