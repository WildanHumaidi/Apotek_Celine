<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PeranSeeder::class, // Harus pertama karena users bergantung padanya
            UserSeeder::class, // Bergantung pada Peran
            PasienSeeder::class,
            ObatSeeder::class,
            PasienDenganResepSeeder::class, // Bergantung pada Pasien
            TransaksiPenjualanSeeder::class, // Bergantung pada Pasien, Users (admin), Obat
            DetailTransaksiSeeder::class, // Bergantung pada TransaksiPenjualan, Obat
        ]);
    }
}