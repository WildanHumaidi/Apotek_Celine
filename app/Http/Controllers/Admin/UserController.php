<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Import model User
use App\Models\Peran; // Import model Peran
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Validation\Rule; // Untuk validasi unique email saat update
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        // Mengambil semua user dengan relasi peran mereka, diurutkan berdasarkan nama
        $users = User::with('peran')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan form untuk menambah pengguna baru.
     */
    public function create()
    {
        // Mengambil semua peran untuk dropdown pilihan peran
        $perans = Peran::all();
        return view('admin.users.create', compact('perans'));
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Email harus unik
            'password' => 'required|string|min:8|confirmed', // Password minimal 8 karakter dan harus cocok dengan password_confirmation
            'phone_number' => 'nullable|string|max:20', // No. telepon opsional
            'id_peran' => 'required|exists:peran,id', // id_peran harus ada di tabel peran
        ]);

        // Buat user baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password sebelum disimpan
            'phone_number' => $request->phone_number,
            'id_peran' => $request->id_peran,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource. (Opsional)
     * Untuk menampilkan detail satu pengguna (jika diperlukan)
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Menampilkan form untuk mengedit pengguna yang sudah ada.
     */
    public function edit(User $user)
    {
        $perans = Peran::all(); // Ambil semua peran untuk dropdown
        return view('admin.users.edit', compact('user', 'perans'));
    }

    /**
     * Memperbarui data pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        // Validasi input dari form update
        $request->validate([
            'name' => 'required|string|max:255',
            // Email harus unik, kecuali jika email tersebut adalah email user yang sedang diedit
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed', // Password bisa kosong jika tidak ingin diubah
            'phone_number' => 'nullable|string|max:20',
            'id_peran' => 'required|exists:peran,id',
        ]);

        // Update data user
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->id_peran = $request->id_peran;

        // Hanya update password jika field password diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save(); // Simpan perubahan

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna dari storage.
     */
    public function destroy(User $user)
    {
        // Pencegahan: Admin tidak dapat menghapus akunnya sendiri
        if (Auth::id() == $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete(); // Hapus user

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}