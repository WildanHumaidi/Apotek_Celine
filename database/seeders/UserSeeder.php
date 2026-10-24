<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID peran yang sudah ada
        $adminRole = DB::table('peran')->where('nama_peran', 'admin')->first();
        $apotekerRole = DB::table('peran')->where('nama_peran', 'apoteker')->first();
        $pasienRole = DB::table('peran')->where('nama_peran', 'pasien')->first(); // Ambil peran pasien

        DB::table('users')->insert([
            [
                'id_peran' => $adminRole->id ?? null,
                'name' => 'Admin Utama',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '081234567890',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_peran' => $apotekerRole->id ?? null,
                'name' => 'Apoteker Budi',
                'email' => 'budi.apoteker@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '087654321098',
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'id_peran' => $pasienRole->id ?? null, // Tambahkan user dengan peran pasien
                'name' => 'Pasien Siti',
                'email' => 'siti.pasien@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '089876543210',
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            // Tambahkan user lain jika diperlukan
        ]);
    }
}