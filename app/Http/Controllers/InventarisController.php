<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class InventarisController extends Controller
{
    public function index()
    {
        // 1. Ambil data produk, urutkan dari stok terbanyak
        $produk = Produk::orderBy('stok', 'desc')->get();

        // 2. Cari stok tertinggi untuk acuan lebar grafik (bar)
        // Jika data kosong, set 0 agar tidak error pembagian
        $maxStock = $produk->max('stok');
        $maxStock = $maxStock == 0 ? 1 : $maxStock;

        return view('inventaris.index', compact('produk', 'maxStock'));
    }
}
