@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('header_title', 'Edit Produk')

@section('content')
<div class="flex justify-center">
    <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-4xl relative">

        <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
            <a href="{{ route('inventaris.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Data Produk</h2>
                <p class="text-sm text-gray-400">Perbarui informasi produk di sini.</p>
            </div>
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
                            <span class="text-xs font-normal mt-1 opacity-70">Klik untuk upload</span>
                        </div>

                        <input type="file" name="gambar" id="input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Biarkan kosong jika tidak berubah.</p>
                </div>

                <div class="md:col-span-2 space-y-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Produk</label>
                        <input type="text" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Kategori</label>
                            <select name="id_kategori" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 cursor-pointer" required>
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
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                            <input type="number" name="harga_produk" value="{{ old('harga_produk', $produk->harga_produk) }}" class="w-full p-3 pl-10 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi</label>
                        <textarea name="deskripsi_produk" rows="3" class="w-full p-4 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('inventaris.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</a>
                <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-xl hover:bg-yellow-600 transition font-bold shadow-lg shadow-yellow-200 flex items-center">
                    <i class="fa-solid fa-check mr-2"></i> Update Produk
                </button>
            </div>
        </form>
    </div>
</div>

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
                previewImg.classList.remove('hidden');
                placeholderIcon.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection