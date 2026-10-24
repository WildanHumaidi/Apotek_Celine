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
        Schema::create('peran', function (Blueprint $table) {
            $table->id(); // ID peran
            $table->string('nama_peran')->unique(); // e.g., 'admin', 'apoteker'
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Seed beberapa peran awal (opsional, bisa di Seeder)
        \DB::table('peran')->insert([
            ['nama_peran' => 'admin', 'deskripsi' => 'Administrator dengan akses penuh sistem', 'created_at' => now(), 'updated_at' => now()],
            ['nama_peran' => 'apoteker', 'deskripsi' => 'Apoteker yang mengelola inventaris obat dan penjualan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_peran' => 'pasien', 'deskripsi' => 'Pengguna yang terdaftar sebagai pasien', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peran');
    }
};