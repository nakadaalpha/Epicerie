<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    // Relasi ke User (Kasir)
    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_user_kasir', 'id_user');
    }
}
