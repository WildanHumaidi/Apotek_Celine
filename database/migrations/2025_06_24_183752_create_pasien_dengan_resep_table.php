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
        Schema::create('pasien_dengan_resep', function (Blueprint $table) {
            $table->id('id_resep'); // Primary key
            $table->unsignedBigInteger('id_pasien'); // Foreign key ke Pasien
            $table->string('nama_pasien');
            $table->string('nama_dokter');
            $table->integer('umur');
            $table->string('alamat');
            $table->string('no_telp');
            $table->timestamps();

            $table->foreign('id_pasien')->references('id_pasien')->on('pasien')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien_dengan_resep');
    }
};