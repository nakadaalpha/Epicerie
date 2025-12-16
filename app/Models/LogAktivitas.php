<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas'; // Sesuai ERD
    protected $primaryKey = 'id_log'; // Sesuai ERD
    public $timestamps = false; // Karena di ERD hanya ada waktu_aktivitas manual

    protected $fillable = [
        'id_user',
        'waktu_aktivitas',
        'jenis_aktivitas'
    ];
}
