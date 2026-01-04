<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    // --- 1. INDEX ---
    public function index()
    {
        $sliders = Slider::orderBy('urutan', 'asc')->get();
        return view('slider.index', compact('sliders'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        return view('slider.create');
    }

    // --- 3. STORE ---
    public function store(Request $request)
    {
        $request->validate([
            'judul'     => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'urutan'    => 'required|integer',
            'gambar'    => 'required|image|mimes:jpeg,png,jpg,webp|max:2048' // Max 2MB
        ]);

        $data = $request->all();
        // Default is_active true jika tidak ada di request
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('sliders', 'public');
        }

        Slider::create($data);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil ditambahkan!');
    }

    // --- 4. EDIT ---
    public function edit($id)
    {
        $slider = Slider::findOrFail($id);
        return view('slider.edit', compact('slider'));
    }

    // --- 5. UPDATE ---
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul'     => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'urutan'    => 'required|integer',
            'gambar'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $slider = Slider::findOrFail($id);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // Cek Gambar Baru
        if ($request->hasFile('gambar')) {
            if ($slider->gambar) {
                Storage::disk('public')->delete($slider->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('sliders', 'public');
        }

        $slider->update($data);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil diperbarui!');
    }

    // --- 6. DESTROY ---
    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);

        if ($slider->gambar) {
            Storage::disk('public')->delete($slider->gambar);
        }

        $slider->delete();
        return redirect()->route('slider.index')->with('success', 'Slider berhasil dihapus!');
    }
}
