<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Peran;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApotekWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan peran terdefinisi (telah di-seed pada migrasi)
        Peran::firstOrCreate(['nama_peran' => 'admin'], ['deskripsi' => 'Admin']);
        Peran::firstOrCreate(['nama_peran' => 'apoteker'], ['deskripsi' => 'Apoteker']);
        Peran::firstOrCreate(['nama_peran' => 'pasien'], ['deskripsi' => 'Pasien']);
    }

    public function test_pasien_model_and_checkout_calculates_price_from_database(): void
    {
        $user = User::create([
            'name' => 'Pasien Test',
            'email' => 'pasien.test@example.com',
            'password' => bcrypt('password'),
            'id_peran' => 3,
        ]);

        $pasien = Pasien::create([
            'id_pasien' => $user->id,
            'nama_pasien' => $user->name,
            'alamat' => 'Jl. Uji No. 1',
            'no_telp' => '08123456789',
        ]);

        $this->assertEquals($user->id, $pasien->id_pasien);

        $obat = Obat::create([
            'nama_obat' => 'Paracetamol 500mg',
            'harga' => 10000.00,
            'kategori' => 'Analgesik',
            'stok' => 50,
        ]);

        // Simulasikan keranjang belanja dengan harga lama (Rp 5.000)
        $sessionCart = [
            $obat->id_obat => [
                'id_obat' => $obat->id_obat,
                'nama_obat' => $obat->nama_obat,
                'harga' => 5000.00, // Harga lama di sesi
                'jumlah' => 3,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $sessionCart])
            ->post(route('pasien.checkout.process'), [
                'metode_pembayaran' => 'transfer_bank_ambil_apotek',
                'confirmPayment' => '1',
            ]);

        $response->assertRedirect(route('pasien.riwayat_pesanan'));

        // Verifikasi transaksi dibuat dengan harga resmi DB (3 * 10000 = 30000), bukan harga sesi (15000)
        $this->assertDatabaseHas('transaksi_penjualan', [
            'id_pasien' => $user->id,
            'harga_bayar' => 30000.00,
            'status_pesanan' => 'menunggu_pembayaran',
        ]);

        // Verifikasi stok terpotong dari 50 menjadi 47
        $obat->refresh();
        $this->assertEquals(47, $obat->stok);
    }

    public function test_cancelling_order_restores_medicine_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password'),
            'id_peran' => 1,
        ]);

        $pasienUser = User::create([
            'name' => 'Pasien Budi',
            'email' => 'budi.pasien@example.com',
            'password' => bcrypt('password'),
            'id_peran' => 3,
        ]);

        $pasien = Pasien::create([
            'id_pasien' => $pasienUser->id,
            'nama_pasien' => $pasienUser->name,
            'alamat' => 'Jl. Sehat No. 2',
            'no_telp' => '0822222222',
        ]);

        $obat = Obat::create([
            'nama_obat' => 'Amoxicillin 500mg',
            'harga' => 15000.00,
            'kategori' => 'Antibiotik',
            'stok' => 20, // Stok awal
        ]);

        // Buat transaksi dengan 5 item
        $noTrx = 'TRX-TEST0001';
        $transaksi = TransaksiPenjualan::create([
            'no_transaksi' => $noTrx,
            'tgl_transaksi' => now()->toDateString(),
            'id_pasien' => $pasien->id_pasien,
            'id_admin' => $admin->id,
            'jumlah' => 5,
            'id_obat' => $obat->id_obat,
            'harga_bayar' => 75000.00,
            'metode_pembayaran' => 'transfer_bank_ambil_apotek',
            'status_pesanan' => 'menunggu_pembayaran',
        ]);

        DetailTransaksi::create([
            'no_transaksi' => $noTrx,
            'tgl_transaksi' => now()->toDateString(),
            'harga_bayar' => 75000.00,
            'jumlah' => 5,
            'id_obat' => $obat->id_obat,
        ]);

        $obat->decrement('stok', 5);
        $this->assertEquals(15, $obat->fresh()->stok);

        // Admin membatalkan pesanan
        $response = $this->actingAs($admin)
            ->patch(route('admin.transaksi_penjualan.update_status', $noTrx), [
                'status_pesanan' => 'dibatalkan',
            ]);

        $response->assertRedirect(route('admin.transaksi_penjualan.show', $noTrx));

        // Verifikasi stok kembali ke 20
        $this->assertEquals(20, $obat->fresh()->stok);
        $this->assertEquals('dibatalkan', $transaksi->fresh()->status_pesanan);
    }
}
