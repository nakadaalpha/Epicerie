<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $guarded = [];

    // Relasi ke PEMBELI
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user_pembeli', 'id_user');
    }

    // Relasi ke KURIR/KARYAWAN (Kolom Baru)
    public function kurir()
    {
        return $this->belongsTo(User::class, 'id_karyawan', 'id_user');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
}