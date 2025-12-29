<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 flex justify-center items-center min-h-[85vh]">
        <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-4xl">

            <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
                <a href="{{ route('inventaris.index') }}" class="mr-4 text-blue-600 hover:text-blue-800 transition transform hover:-translate-x-1">
    <i class="fa-solid fa-arrow-left text-xl"></i>
</a>
                <h2 class="text-2xl font-bold text-gray-800">Edit Produk</h2>
            </div>

            <form action="{{ route('produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <div class="md:col-span-1">
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Foto Produk</label>

                        <div id="drop-zone" class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                            <img id="preview-img"
                                src="{{ $produk->gambar ? asset('storage/' . $produk->gambar) : '' }}"
                                class="absolute inset-0 w-full h-full object-cover z-10 {{ $produk->gambar ? '' : 'hidden' }}" />

                            <div id="placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none px-4 text-center {{ $produk->gambar ? 'hidden' : '' }}">
                                <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-5xl mb-3 transition-transform duration-300"></i>
                                <span class="text-sm font-bold">Ganti Gambar</span>
                                <span class="text-xs font-normal mt-1 opacity-70">Drag & Drop atau Klik</span>
                            </div>

                            <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                        <p class="text-xs text-gray-400 mt-2 text-center">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                    </div>

                    <div class="md:col-span-2 space-y-5">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Produk</label>
                            <input type="text" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Kategori</label>
                                <select name="id_kategori" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                                    <option value="" disabled>Pilih Kategori</option>
                                    @foreach($kategori as $kat)
                                    <option value="{{ $kat->id_kategori }}" {{ $produk->id_kategori == $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Stok</label>
                                <input type="number" name="stok" value="{{ old('stok', $produk->stok) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Harga Satuan</label>
                            <input type="number" name="harga_produk" value="{{ old('harga_produk', $produk->harga_produk) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi</label>
                            <textarea name="deskripsi_produk" rows="3" class="w-full p-4 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('inventaris.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-full hover:bg-yellow-600 transition font-bold shadow-lg flex items-center">
                        <i class="fa-solid fa-check mr-2"></i> Update Produk
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

        // (Anda bisa menambahkan Drag & Drop listener yang sama seperti di file create.blade.php di sini)
    </script>
</body>

</html>