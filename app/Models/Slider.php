<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'sliders';
    protected $primaryKey = 'id_slider';

    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'urutan',
        'is_active'
    ];
}
