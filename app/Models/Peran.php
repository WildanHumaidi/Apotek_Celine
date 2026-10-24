<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    use HasFactory;

    // Nama tabel jika berbeda dari konvensi Laravel (plural dari nama model)
    protected $table = 'peran';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'nama_peran',
        'deskripsi',
    ];
}