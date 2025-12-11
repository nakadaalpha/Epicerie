<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    // Sesuai ERD
    protected $fillable = ['id_kategori', 'nama_produk', 'harga_produk', 'stok', 'deskripsi_produk'];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_produk');
    }
}
