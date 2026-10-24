<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';

    protected $primaryKey = 'id_pasien';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_pasien',
        'nama_pasien',
        'alamat',
        'no_telp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_pasien', 'id');
    }

    public function transaksiPenjualan()
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_pasien', 'id_pasien');
    }

    public function pasienDenganResep()
    {
        return $this->hasMany(PasienDenganResep::class, 'id_pasien', 'id_pasien');
    }
}
