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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peran')->nullable()->default(3);
            $table->string('name'); // Nama pengguna (bisa Nama Admin atau Nama Apoteker)
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone_number')->nullable(); // Untuk nomor telepon, jika ada
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_peran')->references('id')->on('peran')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};