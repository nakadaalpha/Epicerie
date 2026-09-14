<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public function detailTransaksi()
    {
        // Relasi ke tabel detail_transaksi untuk menghitung jumlah terjual
        return $this->hasMany(DetailTransaksi::class, 'id_produk');
    }
    
    public function kategori()
    {
        // Produk 'milik' satu Kategori
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    // 1. Helper untuk cek persentase diskon user saat ini
    public function getPersenDiskonAttribute()
    {
        if (Auth::check()) {
            $level = Auth::user()->membership;

            // Gold & Silver dapat diskon 10%
            if ($level == 'Gold' || $level == 'Silver') {
                return 10;
            }

            // Bronze dapat diskon 5%
            if ($level == 'Bronze') {
                return 5;
            }
        }
        return 0; // Tidak ada diskon
    }

    // 2. Menghitung Harga Akhir
    public function getHargaFinalAttribute()
    {
        // PERBAIKAN: Gunakan 'harga_produk' sesuai nama kolom database Anda
        $hargaAsli = $this->harga_produk;

        $persen = $this->persen_diskon;

        if ($persen > 0) {
            // Rumus: Harga Asli - (Harga Asli * Persen / 100)
            return $hargaAsli - ($hargaAsli * ($persen / 100));
        }

        return $hargaAsli;
    }

    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_produk');
    }
}
