<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::findOrFail($id);
        $rekomendasi = Produk::where('id_produk', '!=', $id)->inRandomOrder()->limit(6)->get();

        return view('produk.detail', compact('produk', 'rekomendasi'));
    }
}
