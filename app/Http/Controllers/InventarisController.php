<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori; // Jangan lupa import Model Kategori

class InventarisController extends Controller
{
    // ... method index() yang sudah ada biarkan saja ...
    public function index()
    {
        $produk = Produk::with('kategori')->orderBy('stok', 'asc')->get();
        $maxStock = $produk->max('stok');
        $maxStock = $maxStock == 0 ? 1 : $maxStock;

        // Pastikan view ini ada di resources/views/inventaris/index.blade.php
        return view('inventaris.index', compact('produk', 'maxStock'));
    }
    public function create()
    {
        $kategori = Kategori::all();

        // UBAH DI SINI:
        // Dari 'produk.create' MENJADI 'inventaris.create'
        return view('inventaris.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'harga_produk' => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
            'deskripsi_produk' => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Validasi gambar (max 2MB)
        ]);

        // 2. Handle Upload Gambar
        if ($request->hasFile('gambar')) {
            // Simpan file ke folder 'public/produk' dan ambil path-nya
            $path = $request->file('gambar')->store('produk', 'public');
            $validatedData['gambar'] = $path;
        }

        // 3. Simpan ke Database
        Produk::create($validatedData);

        return redirect()->route('inventaris')->with('success', 'Produk berhasil ditambahkan!');
    }
}
