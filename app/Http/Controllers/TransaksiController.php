<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function index()
    {
        // Ambil data transaksi, urutkan dari yang terbaru
        // Eager load 'kasir' agar query efisien
        $transaksi = Transaksi::with('kasir')
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        return view('transaksi.index', compact('transaksi'));
    }
}
