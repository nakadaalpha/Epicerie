<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori; // Jangan lupa panggil ini

class ProdukController extends Controller
{
    // 1. LIHAT SEMUA PRODUK
    public function index()
    {
        // Ambil produk beserta nama kategorinya
        $produk = Produk::with('kategori')->get();
        return view('produk.index', compact('produk'));
    }

    // 2. FORM TAMBAH PRODUK
    public function create()
    {
        // Kita butuh data kategori buat isi Dropdown
        $kategori = Kategori::all();
        return view('produk.create', compact('kategori'));
    }

    // 3. SIMPAN PRODUK BARU
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'id_kategori' => 'required', // Wajib pilih kategori
            'harga_produk' => 'required|numeric',
            'stok' => 'required|numeric',
            'deskripsi_produk' => 'required',
        ]);

        Produk::create($request->all());

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    // 4. FORM EDIT PRODUK
    public function edit($id)
    {
        $produk = Produk::find($id);
        $kategori = Kategori::all(); // Kirim data kategori juga buat dropdown
        return view('produk.edit', compact('produk', 'kategori'));
    }

    // 5. UPDATE PRODUK
    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);
        $produk->update($request->all());
        return redirect()->route('produk.index')->with('success', 'Produk berhasil diupdate');
    }

    // 6. HAPUS PRODUK
    public function destroy($id)
    {
        Produk::destroy($id);
        return redirect()->route('produk.index')->with('success', 'Produk dihapus');
    }
}