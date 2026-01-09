@extends('layouts.admin')

@section('title', 'Tambah Slider')
@section('header_title', 'Tambah Banner Baru')

@section('content')
<div class="flex justify-center">
    <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-4xl">

        <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
            <a href="{{ route('slider.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Form Banner</h2>
        </div>

        <form action="{{ route('slider.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Gambar Banner (Landscape)</label>
                    <div id="drop-zone" class="relative w-full h-64 border-2 border-dashed border-gray-300 rounded-3xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition hover:border-blue-400">
                        <img id="preview-img" class="absolute inset-0 w-full h-full object-cover hidden z-10" />
                        <div class="text-center text-gray-400 pointer-events-none z-0 px-4">
                            <i class="fa-regular fa-image text-5xl mb-3"></i>
                            <p class="text-sm font-bold">Upload Banner</p>
                            <p class="text-xs mt-1">Rekomendasi rasio 3:1</p>
                        </div>
                        <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Judul Promo</label>
                    <input type="text" name="judul" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" placeholder="Contoh: Promo Natal">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Urutan</label>
                    <input type="number" name="urutan" value="1" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" placeholder="Keterangan tambahan...">
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 w-full">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-bold text-gray-700">Aktifkan Slider</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end mt-8 gap-4 pt-6 border-t border-gray-100">
                <a href="{{ route('slider.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 font-bold">Batal</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-md transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('input-file').onchange = function(evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('preview-img').src = URL.createObjectURL(file);
            document.getElementById('preview-img').classList.remove('hidden');
        }
    };
</script>
@endsection