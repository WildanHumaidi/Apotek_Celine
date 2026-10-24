<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Peran;
use App\Models\Pasien; // Pastikan ini di-import
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Pastikan ini di-import

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     * Menambahkan validasi untuk alamat dan no_telp
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'alamat' => ['required', 'string', 'max:255'], // <<< VALIDASI BARU
            'no_telp' => ['required', 'string', 'max:20'], // <<< VALIDASI BARU
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     * Menyimpan data ke tabel users dan pasien
     */
    protected function create(array $data)
    {
        DB::beginTransaction();

        try {
            $pasienRole = Peran::where('nama_peran', 'pasien')->first();

            // 1. Buat entri User
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'id_peran' => $pasienRole ? $pasienRole->id : null,
                'phone_number' => $data['no_telp'], // Opsional: Simpan juga no_telp di tabel users jika diinginkan
            ]);

            // 2. Jika user adalah 'pasien', buat juga entri di tabel 'pasien'
            if ($pasienRole && $user->id_peran === $pasienRole->id) {
                Pasien::create([
                    'id_pasien' => $user->id, // ID pasien sama dengan ID user
                    'nama_pasien' => $user->name,
                    'alamat' => $data['alamat'], // <<< SIMPAN ALAMAT DARI FORM
                    'no_telp' => $data['no_telp'], // <<< SIMPAN NO_TELP DARI FORM
                ]);
            }

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal registrasi user dan pasien: ' . $e->getMessage());
            return back()->with('error', 'Registrasi gagal: ' . $e->getMessage()); // Tampilkan pesan error ke user
        }
    }
}