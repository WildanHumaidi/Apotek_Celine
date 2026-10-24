<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasienDenganResep extends Model
{
    use HasFactory;
    protected $table = 'pasien_dengan_resep';
    protected $primaryKey = 'id_resep'; // Sesuaikan dengan PK di migrasi
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_pasien', 'nama_pasien', 'nama_dokter', 'umur', 'alamat', 'no_telp'
    ];

    // Relasi ke Pasien
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien', 'id_pasien');
    }
}