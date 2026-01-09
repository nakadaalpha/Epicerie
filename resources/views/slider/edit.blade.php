@extends('layouts.admin')

@section('title', 'Edit Slider')
@section('header_title', 'Edit Banner Slider')

@section('content')
<div class="flex justify-center">
    <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-4xl">

        <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
            <a href="{{ route('slider.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Banner</h2>
                <p class="text-sm text-gray-400">Perbarui informasi atau ganti gambar banner.</p>
            </div>
        </div>

        <form action="{{ route('slider.update', $slider->id_slider) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Gambar Banner</label>

                    <div id="drop-zone" class="relative w-full h-64 border-2 border-dashed border-gray-300 rounded-3xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                        <img id="preview-img"
                            src="{{ asset('storage/' . $slider->gambar) }}"
                            class="absolute inset-0 w-full h-full object-cover z-10 transition-opacity duration-300" />

                        <div id="placeholder-icon" class="flex flex-col items-center text-gray-500 bg-white/80 backdrop-blur-sm p-4 rounded-xl shadow-sm opacity-0 group-hover:opacity-100 z-20 transition-all duration-300 absolute">
                            <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-4xl mb-2 text-blue-500"></i>
                            <span class="text-sm font-bold text-gray-700">Ganti Gambar</span>
                            <span class="text-xs text-gray-500">Klik atau Drag & Drop</span>
                        </div>

                        <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Format: JPG/PNG. Biarkan jika tidak ingin mengubah gambar.</p>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Judul Promo</label>
                    <input type="text" name="judul" value="{{ old('judul', $slider->judul) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" placeholder="Contoh: Promo Spesial">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Urutan Tampil</label>
                    <input type="number" name="urutan" value="{{ old('urutan', $slider->urutan) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" value="{{ old('deskripsi', $slider->deskripsi) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" placeholder="Keterangan tambahan...">
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 w-full hover:bg-gray-100 transition">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $slider->is_active ? 'checked' : '' }}>
                        <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-bold text-gray-700">Tampilkan Slider (Status Aktif)</span>
                    </label>
                </div>

            </div>

            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('slider.index') }}" class="text-gray-500 hover:text-gray-700 font-bold px-4 py-2 transition">Batal</a>
                <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-xl hover:bg-yellow-600 transition font-bold shadow-lg shadow-yellow-100 flex items-center transform hover:scale-105 duration-200">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Update Slider
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script untuk Preview Gambar --}}
<script>
    const inputFile = document.getElementById('input-file');
    const previewImg = document.getElementById('preview-img');
    const placeholderIcon = document.getElementById('placeholder-icon');

    inputFile.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                // Pastikan opacity penuh saat gambar baru dipilih
                previewImg.classList.remove('opacity-50');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection