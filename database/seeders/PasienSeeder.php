<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pasien')->insert([
            [
                'nama_pasien' => 'Slamet Wijaya',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'no_telp' => '08111223344',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_pasien' => 'Kartika Sari',
                'alamat' => 'Jl. Diponegoro No. 5, Bandung',
                'no_telp' => '08222334455',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_pasien' => 'Rizky Pratama',
                'alamat' => 'Perumahan Indah Blok C2, Surabaya',
                'no_telp' => '08555667788',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}