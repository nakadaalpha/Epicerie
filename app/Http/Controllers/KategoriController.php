<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage; // Penting untuk hapus file lama

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::withCount('produk')->get();
        return view('kategori.index', compact('kategori'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    // --- STORE (Simpan Baru) ---
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi gambar
        ]);

        $data = $request->all();

        // Upload Gambar
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        }

        Kategori::create($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('kategori.edit', compact('kategori'));
    }

    // --- UPDATE (Edit Data) ---
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $kategori = Kategori::findOrFail($id);
        $data = $request->all();

        // Cek jika ada gambar baru yang diupload
        if ($request->hasFile('gambar')) {
            // 1. Hapus gambar lama jika ada
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            // 2. Upload gambar baru
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        }

        $kategori->update($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    // --- DESTROY (Hapus Data) ---
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->produk()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Masih ada produk di kategori ini.');
        }

        // Hapus file gambar dari penyimpanan
        if ($kategori->gambar) {
            Storage::disk('public')->delete($kategori->gambar);
        }

        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
