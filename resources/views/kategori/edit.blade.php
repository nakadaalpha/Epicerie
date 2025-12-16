<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 flex justify-center items-center min-h-[85vh]">
        <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-4xl">

            <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
                <a href="{{ route('kategori.index') }}" class="mr-4 text-blue-600 hover:text-blue-800 transition transform hover:-translate-x-1">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">Edit Kategori</h2>
            </div>

            <form action="{{ route('kategori.update', $kategori->id_kategori) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <div class="md:col-span-1">
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Ikon Kategori</label>

                        <div id="drop-zone"
                            class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-3xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                            <img id="preview-img"
                                src="{{ $kategori->gambar ? asset('storage/' . $kategori->gambar) : '' }}"
                                class="absolute inset-0 w-full h-full object-cover z-10 {{ $kategori->gambar ? '' : 'hidden' }}" />

                            <div id="placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none px-4 text-center {{ $kategori->gambar ? 'hidden' : '' }}">
                                <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-5xl mb-3 text-gray-400/80 transition-transform duration-300"></i>
                                <span class="text-gray-500 font-bold text-base">Ganti Gambar</span>
                                <span class="text-gray-400 text-sm mt-1">Drag & drop atau klik</span>
                            </div>

                            <input type="file" name="gambar" id="input-file" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                        <p class="text-xs text-gray-400 mt-2 text-center">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                    </div>

                    <div class="md:col-span-2 space-y-6">

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Kategori</label>
                            <div class="relative">
                                <i class="fa-solid fa-tag absolute left-4 top-3.5 text-gray-400"></i>
                                <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                                    class="w-full p-3 pl-10 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" required>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="text-blue-700 font-bold text-sm">Informasi</h4>
                                <p class="text-blue-600 text-xs mt-1 leading-relaxed">
                                    Mengubah nama kategori tidak akan menghapus produk di dalamnya, namun akan mengubah label kategori pada produk tersebut.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('kategori.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-full hover:bg-yellow-600 transition font-bold shadow-lg flex items-center transform hover:scale-105 duration-200">
                        <i class="fa-solid fa-check mr-2"></i> Update Kategori
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
        const uploadIcon = document.getElementById('upload-icon');

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

        // Efek Drag & Drop (Optional, agar lebih interaktif)
        ['dragover', 'dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => e.preventDefault());
        });
        dropZone.addEventListener('dragover', () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
            uploadIcon.classList.add('animate-bounce', 'text-blue-500');
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            uploadIcon.classList.remove('animate-bounce', 'text-blue-500');
        });
        dropZone.addEventListener('drop', (e) => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            uploadIcon.classList.remove('animate-bounce', 'text-blue-500');
            if (e.dataTransfer.files.length > 0) {
                inputFile.files = e.dataTransfer.files;
                const event = new Event('change');
                inputFile.dispatchEvent(event);
            }
        });
    </script>
</body>

</html>