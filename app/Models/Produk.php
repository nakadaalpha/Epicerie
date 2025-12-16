<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $guarded = [];

    protected $fillable = [
        'nama_produk',
        'id_kategori',
        'harga_produk',
        'stok',
        'gambar',
        'deskripsi_produk'
        
    ];

    // === TAMBAHAN BARU ===
    public function kategori()
    {
        // Produk 'milik' satu Kategori
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}
