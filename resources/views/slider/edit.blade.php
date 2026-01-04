<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Slider - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 flex justify-center items-center min-h-[85vh]">
        <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-4xl">

            <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
                <a href="{{ route('slider.index') }}" class="mr-4 text-blue-600 hover:text-blue-800 transition transform hover:-translate-x-1">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">Edit Slider</h2>
            </div>

            <form action="{{ route('slider.update', $slider->id_slider) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Gambar Banner</label>
                        <div id="drop-zone" class="relative w-full h-64 border-2 border-dashed border-gray-300 rounded-3xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                            <img id="preview-img" src="{{ asset('storage/' . $slider->gambar) }}" class="absolute inset-0 w-full h-full object-cover z-10" />

                            <div id="placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none px-4 text-center hidden">
                                <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-5xl mb-3"></i>
                                <span class="text-sm font-bold">Ganti Gambar</span>
                            </div>

                            <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                        <p class="text-xs text-gray-400 mt-2 text-center">Klik atau drag gambar baru untuk mengganti.</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Judul Promo</label>
                        <input type="text" name="judul" value="{{ old('judul', $slider->judul) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Urutan Tampil</label>
                        <input type="number" name="urutan" value="{{ old('urutan', $slider->urutan) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi Singkat</label>
                        <input type="text" name="deskripsi" value="{{ old('deskripsi', $slider->deskripsi) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $slider->is_active ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Tampilkan Slider (Aktif)</span>
                        </label>
                    </div>

                </div>

                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('slider.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-full hover:bg-yellow-600 transition font-bold shadow-lg flex items-center transform hover:scale-105 duration-200">
                        <i class="fa-solid fa-check mr-2"></i> Update Slider
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const inputFile = document.getElementById('input-file');
        const previewImg = document.getElementById('preview-img');
        const placeholderIcon = document.getElementById('placeholder-icon');

        inputFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholderIcon.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Hover Effect untuk Edit
        dropZone.addEventListener('mouseover', () => {
            if (!previewImg.classList.contains('hidden')) {
                previewImg.classList.add('opacity-50');
                placeholderIcon.classList.remove('hidden');
            }
        });
        dropZone.addEventListener('mouseleave', () => {
            if (!previewImg.classList.contains('hidden')) {
                previewImg.classList.remove('opacity-50');
                placeholderIcon.classList.add('hidden');
            }
        });
    </script>
</body>

</html>