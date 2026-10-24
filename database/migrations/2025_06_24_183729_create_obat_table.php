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
        Schema::create('obat', function (Blueprint $table) {
            $table->id('id_obat'); // Primary key
            $table->string('nama_obat');
            $table->decimal('harga', 10, 2);
            $table->string('kategori');
            $table->integer('stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};