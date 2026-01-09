@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('header_title', 'Tambah Produk Baru')

@section('content')
<div class="flex justify-center">
    <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-4xl relative">

        <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
            <a href="{{ route('inventaris.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Form Produk Baru</h2>
                <p class="text-sm text-gray-400">Masukkan detail produk dengan lengkap.</p>
            </div>
        </div>

        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-1">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Foto Produk</label>
                    <div id="drop-zone" class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                        <button type="button" id="remove-img-btn" class="absolute top-3 right-3 bg-white text-red-500 rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-red-50 z-30 hidden">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                        <img id="preview-img" class="absolute inset-0 w-full h-full object-cover hidden z-10" />

                        <div id="placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none px-4 text-center">
                            <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-5xl mb-3 transition-transform duration-300"></i>
                            <span class="text-sm font-bold">Upload Gambar</span>
                            <span class="text-[10px] font-normal mt-1 opacity-70">Klik atau Drag di sini</span>
                        </div>

                        <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Format: JPG, PNG (Max 2MB)</p>
                </div>

                <div class="md:col-span-2 space-y-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Produk</label>
                        <input type="text" name="nama_produk" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" placeholder="Contoh: Kopi Susu" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Kategori</label>
                            <select name="id_kategori" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 cursor-pointer" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                @foreach($kategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Stok Awal</label>
                            <input type="number" name="stok" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" placeholder="0" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Harga Satuan</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                            <input type="number" name="harga_produk" class="w-full p-3 pl-12 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 text-right" placeholder="0" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi</label>
                        <textarea name="deskripsi_produk" rows="3" class="w-full p-4 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('inventaris.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition font-bold shadow-lg shadow-blue-200 flex items-center">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script Drag & Drop --}}
<script>
    const dropZone = document.getElementById('drop-zone');
    const inputFile = document.getElementById('input-file');
    const previewImg = document.getElementById('preview-img');
    const placeholderIcon = document.getElementById('placeholder-icon');
    const removeBtn = document.getElementById('remove-img-btn');

    inputFile.addEventListener('change', function() {
        if (this.files.length > 0) showPreview(this.files[0]);
    });

    removeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        inputFile.value = '';
        previewImg.src = '';
        previewImg.classList.add('hidden');
        placeholderIcon.classList.remove('hidden');
        removeBtn.classList.add('hidden');
    });

    // Simple Drag Over Effects
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        if (e.dataTransfer.files.length > 0) {
            inputFile.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    function showPreview(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                placeholderIcon.classList.add('hidden');
                removeBtn.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection