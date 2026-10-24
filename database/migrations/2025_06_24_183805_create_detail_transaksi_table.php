<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; // BENAR: Menggunakan backslash '\'
use Illuminate\Support\Facades\Schema; // BENAR: Menggunakan backslash '\'

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id(); // Primary key untuk detail
            $table->string('no_transaksi'); // Foreign key ke Transaksi Penjualan
            $table->date('tgl_transaksi');
            $table->decimal('harga_bayar', 10, 2);
            $table->integer('jumlah');
            $table->unsignedBigInteger('id_obat'); // Foreign key ke Obat
            $table->timestamps();

            $table->foreign('no_transaksi')->references('no_transaksi')->on('transaksi_penjualan')->onDelete('cascade');
            $table->foreign('id_obat')->references('id_obat')->on('obat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};