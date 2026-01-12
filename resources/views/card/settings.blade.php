@extends('layouts.admin')

@section('title', 'Desain Kartu')
@section('header_title', 'Pengaturan Kartu Member')

@section('content')

{{-- ALERT SUCCESS --}}
@if(session('success'))
<div id="alert" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl shadow-sm flex items-center gap-3 animate-fade-in-down">
    <div class="bg-green-100 p-2 rounded-full"><i class="fa-solid fa-check"></i></div>
    <div>
        <p class="font-bold">Berhasil!</p>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
    <button onclick="document.getElementById('alert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fa-solid fa-xmark"></i></button>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- KOLOM KIRI: FORM UPLOAD --}}
    <div class="lg:col-span-2 space-y-8">

        <form action="{{ route('card.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 1. KARTU DEPAN --}}
            <div class="bg-white rounded-[2rem] p-8 shadow-xl border border-white/40 mb-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 text-9xl text-blue-500 -rotate-12 group-hover:rotate-0 transition duration-700 pointer-events-none">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <div class="flex justify-between items-center mb-6 relative z-10">
                    <h3 class="font-bold text-gray-800 text-xl flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-image"></i></span>
                        Sisi Depan (Utama)
                    </h3>
                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold border border-blue-100">Wajib</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6 relative z-10">
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-2">Upload Gambar Baru</label>
                        <div class="relative border-2 border-dashed border-gray-300 bg-gray-50 rounded-2xl p-6 text-center hover:bg-blue-50 hover:border-blue-300 transition group/upload cursor-pointer">
                            <input type="file" name="bg_front" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this, 'preview-front')">
                            <div class="space-y-2 pointer-events-none">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 group-hover/upload:text-blue-500 transition"></i>
                                <p class="text-xs text-gray-500 font-bold">Klik atau drag file PNG disini</p>
                                <p class="text-[10px] text-gray-400">Rekomendasi: 1026 x 648 px</p>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Area --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-2">Preview Saat Ini</label>
                        <div class="aspect-[85.6/53.98] rounded-xl overflow-hidden shadow-lg border border-gray-200 relative bg-gray-100">
                            {{-- Time() agar cache refresh saat update --}}
                            <img id="preview-front" src="{{ asset('images/card_bg.png') }}?v={{ time() }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/1026x648/111/FFF?text=No+Image'">

                            {{-- Mockup Text Overlay (Agar admin ada gambaran) --}}
                            <div class="absolute inset-0 p-4 pointer-events-none">
                                <div class="font-bold text-white text-lg drop-shadow-md">ÉPICERIE</div>
                                <div class="absolute bottom-4 left-4">
                                    <div class="w-24 h-3 bg-white/30 rounded mb-1 backdrop-blur-sm"></div>
                                    <div class="w-16 h-2 bg-white/30 rounded backdrop-blur-sm"></div>
                                </div>
                                <div class="absolute bottom-4 right-4 w-10 h-10 bg-white rounded flex items-center justify-center">
                                    <i class="fa-solid fa-qrcode text-xl text-black"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. KARTU BELAKANG --}}
            <div class="bg-white rounded-[2rem] p-8 shadow-xl border border-white/40 mb-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 text-9xl text-gray-500 rotate-12 group-hover:rotate-0 transition duration-700 pointer-events-none">
                    <i class="fa-solid fa-rotate"></i>
                </div>

                <div class="flex justify-between items-center mb-6 relative z-10">
                    <h3 class="font-bold text-gray-800 text-xl flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-image"></i></span>
                        Sisi Belakang
                    </h3>
                    <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">Opsional</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6 relative z-10">
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-2">Upload Gambar Baru</label>
                        <div class="relative border-2 border-dashed border-gray-300 bg-gray-50 rounded-2xl p-6 text-center hover:bg-gray-100 hover:border-gray-400 transition group/upload cursor-pointer">
                            <input type="file" name="bg_back" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this, 'preview-back')">
                            <div class="space-y-2 pointer-events-none">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 group-hover/upload:text-gray-600 transition"></i>
                                <p class="text-xs text-gray-500 font-bold">Klik atau drag file PNG disini</p>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Area --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-2">Preview Saat Ini</label>
                        <div class="aspect-[85.6/53.98] rounded-xl overflow-hidden shadow-lg border border-gray-200 relative bg-gray-100">
                            <img id="preview-back" src="{{ asset('images/card_bg_back.png') }}?v={{ time() }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/1026x648/eee/999?text=Kosong'">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOMBOL SIMPAN --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-extrabold py-4 px-10 rounded-2xl shadow-lg shadow-blue-500/30 transform hover:scale-105 transition-all flex items-center gap-3">
                    <i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN
                </button>
            </div>

        </form>
    </div>

    {{-- KOLOM KANAN: PANDUAN --}}
    <div class="space-y-6">
        <div class="bg-blue-600 text-white rounded-[2rem] p-6 shadow-xl relative overflow-hidden">
            <i class="fa-solid fa-circle-info absolute -right-4 -bottom-4 text-9xl opacity-20"></i>
            <h3 class="font-bold text-lg mb-4 relative z-10">Panduan Desain</h3>
            <ul class="space-y-3 text-sm relative z-10">
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-ruler-combined mt-1 text-blue-200"></i>
                    <div>
                        <p class="font-bold">Ukuran Kanvas</p>
                        <p class="text-blue-100 text-xs">1026 x 648 pixel (Rasio 85.6mm x 54mm)</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-file-image mt-1 text-blue-200"></i>
                    <div>
                        <p class="font-bold">Format File</p>
                        <p class="text-blue-100 text-xs">Gunakan format <strong>.PNG</strong> agar warna solid tidak pecah (blur).</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation mt-1 text-blue-200"></i>
                    <div>
                        <p class="font-bold">Zona Aman</p>
                        <p class="text-blue-100 text-xs">Jangan taruh logo/teks di area Nama (Kiri Bawah) dan QR Code (Kanan Bawah) agar tidak tertumpuk.</p>
                    </div>
                </li>
            </ul>
        </div>

        {{-- DOWNLOAD TEMPLATE --}}
        <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 text-center">
            <div class="w-16 h-16 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fa-solid fa-download"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-1">Belum punya desain?</h3>
            <p class="text-xs text-gray-500 mb-4">Gunakan template kosong ini sebagai acuan ukuran.</p>
            <a href="https://placehold.co/1026x648/050505/FFFFFF/png?text=TEMPLATE+UKURAN+1026x648" target="_blank" download class="block w-full py-2 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 transition text-sm">
                Download Template
            </a>
        </div>
    </div>

</div>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@endsection