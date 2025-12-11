<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
