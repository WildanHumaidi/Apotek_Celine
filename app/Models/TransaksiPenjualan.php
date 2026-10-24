<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TransaksiPenjualan extends Model
{
    use HasFactory;
    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'no_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'no_transaksi',
        'tgl_transaksi',
        'id_pasien',
        'id_admin', // Pastikan kolom ini di migrasi juga nullable jika id_admin tidak selalu ada
        'jumlah',
        'id_obat', // Pastikan kolom ini di migrasi juga nullable jika id_obat tidak selalu ada
        'harga_bayar',
        'metode_pembayaran', // <-- KOLOM BARU
        'status_pesanan'     // <-- KOLOM BARU
    ];

    public function pasien() { return $this->belongsTo(Pasien::class, 'id_pasien', 'id_pasien'); }
    public function adminUser() { return $this->belongsTo(User::class, 'id_admin'); }
    public function detailTransaksi() { return $this->hasMany(DetailTransaksi::class, 'no_transaksi', 'no_transaksi'); }
    public function obat() { return $this->belongsTo(Obat::class, 'id_obat', 'id_obat'); }
}