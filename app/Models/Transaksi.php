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


    
    // Kita pakai $fillable biar lebih aman dan spesifik kolom mana yang boleh diisi
    protected $fillable = [
        'id_user_pembeli',
        'id_user_kasir',
        'kode_transaksi',
        'total_bayar',
        'metode_pembayaran',
        'tanggal_transaksi',
        // 👇 Ini kolom baru buat fitur Hold
        'status', 
        'nama_pelanggan_hold',
    ];

    // Relasi ke Detail Transaksi (biar bisa dipanggil $transaksi->detailTransaksi)
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
}