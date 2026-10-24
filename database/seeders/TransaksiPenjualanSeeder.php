<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Untuk UUID jika no_transaksi unik

class TransaksiPenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pasien1 = DB::table('pasien')->where('nama_pasien', 'Slamet Wijaya')->first();
        $pasien2 = DB::table('pasien')->where('nama_pasien', 'Kartika Sari')->first();
        $adminUser = DB::table('users')->where('email', 'admin@example.com')->first();
        $obatParacetamol = DB::table('obat')->where('nama_obat', 'Paracetamol 500mg')->first();
        $obatVitaminC = DB::table('obat')->where('nama_obat', 'Vitamin C 1000mg')->first();

        DB::table('transaksi_penjualan')->insert([
            [
                'no_transaksi' => 'TRX-' . Str::upper(Str::random(6)), // Contoh nomor transaksi unik
                'tgl_transaksi' => '2025-06-20',
                'id_pasien' => $pasien1->id_pasien ?? null,
                'id_admin' => $adminUser->id ?? null,
                'jumlah' => 2, // Total item: 1 Paracetamol + 1 Amoxicillin
                'id_obat' => $obatParacetamol->id_obat ?? null,
                'harga_bayar' => 20000.00, // 5000 + 15000
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no_transaksi' => 'TRX-' . Str::upper(Str::random(6)),
                'tgl_transaksi' => '2025-06-21',
                'id_pasien' => $pasien2->id_pasien ?? null,
                'id_admin' => $adminUser->id ?? null,
                'jumlah' => 2,
                'id_obat' => $obatVitaminC->id_obat ?? null,
                'harga_bayar' => 16000.00, // 2 * 8000
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}