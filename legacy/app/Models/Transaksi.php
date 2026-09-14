<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    // Menggunakan $fillable agar lebih aman dan jelas kolom apa saja yang bisa diisi
    protected $fillable = [
        'kode_transaksi',
        'id_user_pembeli', // ID Pelanggan (Member)
        'id_karyawan',     // ID Kasir atau Kurir yang menangani
        'id_alamat',       // Alamat Pengiriman (Bisa null jika beli di tempat/POS)
        'total_bayar',
        'status',          // 'Dikemas', 'Dikirim', 'Selesai', 'Batal'
        'nama_pelanggan',  // Nama manual (jika pelanggan umum/bukan member)
        'bukti_bayar',     // Untuk transfer
        'snap_token',      // Untuk Midtrans
        'ongkir',          // Ongkos kirim
        'resi'             // Nomor resi pengiriman
    ];

    // Opsional: Casting tipe data agar konsisten
    protected $casts = [
        'total_bayar' => 'integer',
        'ongkir' => 'integer',
        'created_at' => 'datetime',
    ];

    // ==========================================
    // RELASI DATABASE
    // ==========================================

    // 1. Relasi ke PEMBELI (User)
    public function user()
    {
        // Parameter: (Model, Foreign Key di tabel ini, Primary Key di tabel User)
        return $this->belongsTo(User::class, 'id_user_pembeli', 'id_user');
    }

    // 2. Relasi ke KARYAWAN / KURIR
    public function kurir() // Bisa juga dinamakan 'karyawan()'
    {
        return $this->belongsTo(User::class, 'id_karyawan', 'id_user');
    }

    // 3. Relasi ke DETAIL TRANSAKSI (Barang yang dibeli)
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // 4. Relasi ke ALAMAT PENGIRIMAN
    public function alamat()
    {
        return $this->belongsTo(AlamatPengiriman::class, 'id_alamat', 'id_alamat');
    }
}