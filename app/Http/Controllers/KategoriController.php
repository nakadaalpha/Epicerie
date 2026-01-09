<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage; // Penting untuk hapus file

class KategoriController extends Controller
{
    // --- 1. INDEX (READ + SEARCH) ---
    public function index(Request $request)
    {
        // Gunakan withCount untuk menghitung jumlah produk (agar $k->produk_count di view tidak error/0)
        $query = \App\Models\Kategori::withCount('produk');

        if ($request->has('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        // PENTING: Gunakan paginate(), bukan get() atau all()
        // Angka 10 adalah jumlah item per halaman
        $kategori = $query->paginate(10);

        return view('kategori.index', compact('kategori'));
    }

    // --- 2. CREATE (FORM) ---
    public function create()
    {
        return view('kategori.create');
    }

    // --- 3. STORE (SIMPAN BARU) ---
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        }

        Kategori::create($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    // --- 4. EDIT (FORM UPDATE) ---
    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('kategori.edit', compact('kategori'));
    }

    // --- 5. UPDATE (SIMPAN PERUBAHAN) ---
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $kategori = Kategori::findOrFail($id);
        $data = $request->all();

        // Cek jika ada gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama agar storage tidak penuh
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        }

        $kategori->update($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    // --- 6. DESTROY (HAPUS) ---
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Cek relasi: Jangan hapus jika masih ada produk
        if ($kategori->produk()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Masih ada produk di kategori ini.');
        }

        // Hapus file gambar fisik
        if ($kategori->gambar) {
            Storage::disk('public')->delete($kategori->gambar);
        }

        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
