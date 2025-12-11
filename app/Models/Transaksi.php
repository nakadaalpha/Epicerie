<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    // Pastikan casting tanggal aktif untuk memudahkan query "Hari Ini"
    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }
}
