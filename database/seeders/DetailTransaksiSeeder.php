<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transaksi1 = DB::table('transaksi_penjualan')->where('tgl_transaksi', '2025-06-20')->first(); // Ambil transaksi pertama
        $transaksi2 = DB::table('transaksi_penjualan')->where('tgl_transaksi', '2025-06-21')->first(); // Ambil transaksi kedua
        $obatParacetamol = DB::table('obat')->where('nama_obat', 'Paracetamol 500mg')->first();
        $obatAmoxicillin = DB::table('obat')->where('nama_obat', 'Amoxicillin 250mg')->first();
        $obatVitaminC = DB::table('obat')->where('nama_obat', 'Vitamin C 1000mg')->first();

        if ($transaksi1 && $obatParacetamol && $obatAmoxicillin && $obatVitaminC) { // Pastikan semua data ada
            DB::table('detail_transaksi')->insert([
                [
                    'no_transaksi' => $transaksi1->no_transaksi,
                    'tgl_transaksi' => '2025-06-20', // Redundant, tapi sesuai diagram
                    'harga_bayar' => 5000.00, // Harga per item
                    'jumlah' => 1,
                    'id_obat' => $obatParacetamol->id_obat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'no_transaksi' => $transaksi1->no_transaksi,
                    'tgl_transaksi' => '2025-06-20',
                    'harga_bayar' => 15000.00,
                    'jumlah' => 1,
                    'id_obat' => $obatAmoxicillin->id_obat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'no_transaksi' => $transaksi2->no_transaksi,
                    'tgl_transaksi' => '2025-06-21',
                    'harga_bayar' => 16000.00,
                    'jumlah' => 2,
                    'id_obat' => $obatVitaminC->id_obat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}