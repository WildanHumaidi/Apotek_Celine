<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('obat')->insert([
            [
                'nama_obat' => 'Paracetamol 500mg',
                'harga' => 5000.00,
                'kategori' => 'Analgesik',
                'stok' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_obat' => 'Amoxicillin 250mg',
                'harga' => 15000.00,
                'kategori' => 'Antibiotik',
                'stok' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_obat' => 'Vitamin C 1000mg',
                'harga' => 8000.00,
                'kategori' => 'Vitamin',
                'stok' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_obat' => 'Antasida Doen',
                'harga' => 3500.00,
                'kategori' => 'Antasida',
                'stok' => 75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}