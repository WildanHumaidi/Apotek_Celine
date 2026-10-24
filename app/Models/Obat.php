<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;
    protected $table = 'obat';
    protected $primaryKey = 'id_obat';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_obat',
        'deskripsi', // <-- PASTIKAN INI ADA
        'harga',
        'kategori',
        'stok',
        'foto'       // <-- PASTIKAN INI ADA
    ];
}