<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Peran; // Import model Peran

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Peran::updateOrCreate(
            ['nama_peran' => 'admin'],
            ['deskripsi' => 'Administrator dengan akses penuh sistem.']
        );

        Peran::updateOrCreate(
            ['nama_peran' => 'apoteker'],
            ['deskripsi' => 'Apoteker yang mengelola inventaris obat dan penjualan.']
        );

        Peran::updateOrCreate(
            ['nama_peran' => 'pasien'],
            ['deskripsi' => 'Pengguna yang terdaftar sebagai pasien.']
        );
        // created_at dan updated_at akan otomatis diisi oleh Eloquent
    }
}