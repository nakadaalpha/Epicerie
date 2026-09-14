<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    // Pastikan fillable sudah mencakup gambar jika Anda pakai fitur gambar
    protected $fillable = ['nama_kategori', 'gambar'];

    // --- TAMBAHKAN KODE INI ---
    public function produk()
    {
        // Relasi: Satu kategori memiliki banyak produk
        // Parameter 2: Foreign Key di tabel produk (id_kategori)
        // Parameter 3: Primary Key di tabel kategori (id_kategori)
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}
