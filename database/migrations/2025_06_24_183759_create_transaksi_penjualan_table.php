<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; // KOREKSI: dari -> menjadi \
use Illuminate\Support\Facades\Schema; // KOREKSI: dari -> menjadi \

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->string('no_transaksi')->primary(); // Primary key
            $table->date('tgl_transaksi');
            $table->unsignedBigInteger('id_pasien'); // Foreign key ke Pasien
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->integer('jumlah');
            $table->unsignedBigInteger('id_obat');
            $table->decimal('harga_bayar', 10, 2);
            $table->timestamps();

            $table->foreign('id_pasien')->references('id_pasien')->on('pasien')->onDelete('cascade');
            $table->foreign('id_admin')->references('id')->on('users')->onDelete('set null');
            $table->foreign('id_obat')->references('id_obat')->on('obat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan');
    }
};