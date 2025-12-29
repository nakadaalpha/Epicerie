<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlamatPengiriman extends Model
{
    use HasFactory;

    protected $table = 'alamat_pengiriman';

    // PENTING: Definisikan primary key karena bukan 'id'
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_user',
        'label',
        'penerima',        // Sesuaikan dengan SQL (sebelumnya mungkin 'nama_penerima')
        'no_hp_penerima',  // Sesuaikan dengan SQL
        'detail_alamat'
    ];
}
