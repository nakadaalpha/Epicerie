<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage; // WAJIB: Import ini untuk hapus file

class InventarisController extends Controller
{
    // --- 1. INDEX (READ) ---
    public function index()
    {
        $produk = Produk::with('kategori')->orderBy('created_at', 'desc')->get();
        return view('inventaris.index', compact('produk'));
    }

    // --- 2. CREATE (FORM) ---
    public function create()
    {
        $kategori = Kategori::all();
        return view('inventaris.create', compact('kategori'));
    }

    // --- 3. STORE (SIMPAN BARU) ---
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'harga_produk' => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
            'deskripsi_produk' => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Upload Gambar
        if ($request->hasFile('gambar')) {
            // Simpan ke folder: storage/app/public/produk
            $path = $request->file('gambar')->store('produk', 'public');
            $validatedData['gambar'] = $path;
        }

        Produk::create($validatedData);

        return redirect()->route('inventaris.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    // --- 4. EDIT (FORM UPDATE) ---
    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $kategori = Kategori::all();
        return view('inventaris.edit', compact('produk', 'kategori'));
    }

    // --- 5. UPDATE (SIMPAN PERUBAHAN) ---
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'harga_produk' => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
            'deskripsi_produk' => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Cek apakah user upload gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada (Biar storage ga penuh)
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            // Simpan gambar baru
            $path = $request->file('gambar')->store('produk', 'public');
            $validatedData['gambar'] = $path;
        }

        $produk->update($validatedData);

        return redirect()->route('inventaris.index')->with('success', 'Produk berhasil diperbarui!');
    }

    // --- 6. DESTROY (HAPUS) ---
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        // Hapus gambar dari folder storage saat produk dihapus
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()->route('inventaris.index')->with('success', 'Produk berhasil dihapus!');
    }
}