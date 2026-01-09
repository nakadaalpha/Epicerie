@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('header_title', 'Edit Kategori')

@section('content')
    <div class="flex justify-center">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-4xl">

            <div class="flex items-center mb-8 border-b border-gray-100 pb-4">
                <a href="{{ route('kategori.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">Edit Kategori</h2>
            </div>

            <form action="{{ route('kategori.update', $kategori->id_kategori) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Ikon Kategori</label>
                        <div class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition hover:border-blue-400">
                            <img id="preview-img" src="{{ $kategori->gambar ? asset('storage/' . $kategori->gambar) : '' }}" class="absolute inset-0 w-full h-full object-cover z-10 {{ $kategori->gambar ? '' : 'hidden' }}" />
                            <div class="text-center text-gray-400 pointer-events-none z-0">
                                <i class="fa-solid fa-cloud-arrow-up text-4xl mb-2"></i>
                                <p class="text-xs font-bold">Ganti Gambar</p>
                            </div>
                            <input type="file" name="gambar" id="input-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Kategori</label>
                            <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex gap-3 text-sm text-blue-700">
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            <p>Perubahan nama kategori akan berlaku untuk semua produk terkait.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-gray-100 gap-4">
                    <a href="{{ route('kategori.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 font-bold">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-xl font-bold hover:bg-yellow-600 shadow-md transition">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('input-file').onchange = function(evt) {
            const [file] = this.files;
            if (file) document.getElementById('preview-img').src = URL.createObjectURL(file);
            document.getElementById('preview-img').classList.remove('hidden');
        };
    </script>
@endsection