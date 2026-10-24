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
        Schema::table('obat', function (Blueprint $table) {
            // Kolom untuk path foto produk (nullable karena mungkin tidak semua punya foto)
            // Ditempatkan setelah 'stok' agar konsisten dengan urutan input di form.
            $table->string('foto')->nullable()->after('stok');
            // Kolom untuk deskripsi produk (text karena bisa panjang)
            // Ditempatkan setelah 'nama_obat' agar sesuai urutan data produk.
            $table->text('deskripsi')->nullable()->after('nama_obat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('foto');
            $table->dropColumn('deskripsi');
        });
    }
};