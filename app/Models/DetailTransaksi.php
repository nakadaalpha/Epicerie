<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    // 1. Sesuaikan nama tabel
    protected $table = 'detail_transaksi';

    // 2. Sesuaikan Primary Key (Sesuai screenshot lu: id_detail_transaksi)
    protected $primaryKey = 'id_detail_transaksi';

    // 3. Kolom yang boleh diisi
    protected $fillable = [
        'id_transaksi',
        'id_produk',
        'jumlah',
        'harga_produk_saat_beli'
    ];

    // === RELASI (INI YANG BIKIN ERROR SEBELUMNYA) ===
    
    // Relasi ke Produk (Biar bisa ambil nama & gambar produk)
    public function produk()
    {
        // belongsTo(ModelTujuan, Foreign_Key_Di_Sini, Primary_Key_Di_Sana)
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    // Relasi ke Transaksi (Induknya)
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}