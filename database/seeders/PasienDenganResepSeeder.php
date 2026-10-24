<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasienDenganResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID pasien yang sudah ada
        $pasien1 = DB::table('pasien')->where('nama_pasien', 'Slamet Wijaya')->first();
        $pasien2 = DB::table('pasien')->where('nama_pasien', 'Kartika Sari')->first();

        DB::table('pasien_dengan_resep')->insert([
            [
                'id_pasien' => $pasien1->id_pasien ?? null,
                'nama_pasien' => 'Slamet Wijaya', // Redundant, tapi sesuai diagram
                'nama_dokter' => 'Dr. Agung Pratama',
                'umur' => 45,
                'alamat' => 'Jl. Merdeka No. 10, Jakarta', // Redundant, tapi sesuai diagram
                'no_telp' => '08111223344', // Redundant, tapi sesuai diagram
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pasien' => $pasien2->id_pasien ?? null,
                'nama_pasien' => 'Kartika Sari', // Redundant, tapi sesuai diagram
                'nama_dokter' => 'Dr. Rina Handayani',
                'umur' => 30,
                'alamat' => 'Jl. Diponegoro No. 5, Bandung', // Redundant, tapi sesuai diagram
                'no_telp' => '08222334455', // Redundant, tapi sesuai diagram
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}