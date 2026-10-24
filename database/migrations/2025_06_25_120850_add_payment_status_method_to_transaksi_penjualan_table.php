<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->string('metode_pembayaran')->after('harga_bayar')->nullable(); // Tambahkan kolom metode_pembayaran
            $table->string('status_pesanan')->after('metode_pembayaran')->default('menunggu_pembayaran'); // Tambahkan kolom status_pesanan dengan default
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
            $table->dropColumn('status_pesanan');
        });
    }
};