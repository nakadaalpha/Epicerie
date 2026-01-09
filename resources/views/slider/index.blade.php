@extends('layouts.admin')

@section('title', 'Kelola Slider')
@section('header_title', 'Banner Slider')

@section('content')

{{-- Style Khusus --}}
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>

<div class="max-w-7xl mx-auto">

    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Kelola Banner</h2>
                <p class="text-gray-400 text-sm mt-1">Atur gambar promosi di halaman depan.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                Total: {{ $sliders->count() }}
            </span>
        </div>

        <div class="hidden md:flex items-center px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-32">Preview</div>
            <div class="flex-1">Info Promo</div>
            <div class="w-32 text-center">Urutan</div>
            <div class="w-32 text-center">Status</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        <div class="flex flex-col gap-3">

            @forelse($sliders as $s)
            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="flex items-center w-full md:w-32 mb-3 md:mb-0">
                    <div class="relative flex-shrink-0 w-full md:w-28 h-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                        <img src="{{ asset('storage/' . $s->gambar) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </div>
                </div>

                <div class="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                    <h3 class="font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">
                        {{ $s->judul ?? 'Tanpa Judul' }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1 truncate max-w-md">
                        {{ $s->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}
                    </p>
                </div>

                <div class="w-full md:w-32 flex items-center md:justify-center mb-2 md:mb-0">
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 border border-gray-200 px-3 py-1 rounded-lg">
                        Urutan: {{ $s->urutan }}
                    </span>
                </div>

                <div class="w-full md:w-32 flex items-center md:justify-center mb-2 md:mb-0">
                    @if($s->is_active)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-gray-50 text-gray-500 border border-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non-Aktif
                    </span>
                    @endif
                </div>

                <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">

                    <a href="{{ route('slider.edit', $s->id_slider) }}"
                        class="bg-white text-yellow-500 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 hover:bg-yellow-400 hover:text-white hover:border-yellow-400 transition"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </a>

                    <form action="{{ route('slider.destroy', $s->id_slider) }}" method="POST" onsubmit="return confirm('Hapus banner ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-white text-red-500 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                            title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>

                </div>

            </div>
            @empty

            <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                    <i class="fa-regular fa-images text-4xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-600 text-lg">Belum ada slider.</h3>
                <p class="text-sm text-gray-400 mt-1">Tambahkan banner promosi untuk menarik pelanggan.</p>
            </div>

            @endforelse
        </div>

    </div>

</div>

<div class="fixed bottom-8 right-8 z-50">
    <a href="{{ route('slider.create') }}"
        class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
        title="Tambah Slider Baru">
        <i class="fa-solid fa-plus text-2xl"></i>
    </a>
</div>

@endsection