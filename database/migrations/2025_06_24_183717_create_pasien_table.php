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
        Schema::create('pasien', function (Blueprint $table) {
            $table->id('id_pasien'); // Primary key
            $table->string('nama_pasien');
            $table->string('alamat'); // <<< JANGAN ADA ->nullable() jika wajib
            $table->string('no_telp'); // <<< JANGAN ADA ->nullable() jika wajib
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien');
    }
};