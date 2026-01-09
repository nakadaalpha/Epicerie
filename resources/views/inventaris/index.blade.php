@extends('layouts.admin')

@section('title', 'Inventaris Barang')
@section('header_title', 'Manajemen Inventaris')

@section('content')

{{-- Style Khusus Halaman Ini --}}
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
</style>

{{-- Wrapper Utama --}}
<div class="max-w-7xl mx-auto">

    <div class="mb-8">
        <form action="{{ route('inventaris.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">

            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Produk..."
                    class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40">
                <div class="absolute left-4 top-3.5 text-blue-500">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('inventaris.index') }}" class="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition" title="Reset Pencarian">
                    <i class="fa-solid fa-times text-lg"></i>
                </a>
                @endif
            </div>

            <div class="relative min-w-[180px]">
                <select name="kategori" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $kat)
                    <option value="{{ $kat->id_kategori }}" {{ request('kategori') == $kat->id_kategori ? 'selected' : '' }}>
                        {{ $kat->nama_kategori }}
                    </option>
                    @endforeach
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            <div class="relative min-w-[180px]">
                <select name="sort" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="stok_sedikit" {{ request('sort') == 'stok_sedikit' ? 'selected' : '' }}>Stok Paling Sedikit</option>
                    <option value="stok_banyak" {{ request('sort') == 'stok_banyak' ? 'selected' : '' }}>Stok Paling Banyak</option>
                    <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Harga Tertinggi</option>
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none">
                    <i class="fa-solid fa-arrow-down-up-across-line"></i>
                </div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

        </form>
    </div>

    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Daftar Produk</h2>
                <p class="text-gray-400 text-sm mt-1">Kelola stok dan harga produk toko Anda.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                Total: {{ $produk->total() }} Item
            </span>
        </div>

        <div class="hidden md:flex items-center px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-20">Produk</div>
            <div class="flex-1">Nama & Kategori</div>
            <div class="w-32">Harga</div>
            <div class="w-24 text-center">Stok</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        <div class="flex flex-col gap-3">

            @forelse($produk as $p)
            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="flex items-center w-full md:w-20 mb-3 md:mb-0">
                    <div class="relative flex-shrink-0">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-12 h-12 rounded-lg object-cover shadow-sm border border-gray-200">
                        @else
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-bold shadow-sm border border-blue-100">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        @endif

                        @if($p->stok < 10)
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 border-2 border-white rounded-full animate-pulse" title="Stok Kritis">
                    </div>
                    @endif
                </div>
                <div class="md:hidden ml-3">
                    <h3 class="font-bold text-gray-800 text-sm">{{ $p->nama_produk }}</h3>
                </div>
            </div>

            <div class="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                <h3 class="hidden md:block font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">{{ $p->nama_produk }}</h3>
                <div class="flex items-center mt-1">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $p->kategori ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-red-50 text-red-500 border-red-100' }}">
                        {{ $p->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                    </span>
                </div>
            </div>

            <div class="w-full md:w-32 flex items-center mb-2 md:mb-0">
                <span class="font-extrabold text-blue-600 text-sm">Rp {{ number_format($p->harga_produk, 0, ',', '.') }}</span>
            </div>

            <div class="w-full md:w-24 flex md:justify-center items-center mb-2 md:mb-0">
                <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $p->stok < 10 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }}">
                    {{ $p->stok }} Unit
                </span>
            </div>

            <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">
                <a href="{{ route('produk.edit', $p->id_produk) }}"
                    class="bg-white text-yellow-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-yellow-400 hover:text-white hover:border-yellow-400 transition"
                    title="Edit">
                    <i class="fa-solid fa-pen text-xs"></i>
                </a>

                <form action="{{ route('produk.destroy', $p->id_produk) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?');" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-white text-red-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                        title="Hapus">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
            <div class="bg-gray-50 p-6 rounded-full mb-4">
                <i class="fa-solid fa-magnifying-glass-minus text-4xl text-gray-300"></i>
            </div>
            @if(request('search') || request('kategori'))
            <h3 class="font-bold text-gray-600 text-lg">Produk tidak ditemukan.</h3>
            <p class="text-sm text-gray-400 mt-1">Coba kata kunci lain atau reset filter.</p>
            <a href="{{ route('inventaris.index') }}" class="mt-4 text-sm font-bold text-blue-500 hover:underline">Reset Filter</a>
            @else
            <h3 class="font-bold text-gray-600 text-lg">Inventaris Kosong.</h3>
            <p class="text-sm text-gray-400 mt-1">Belum ada produk yang ditambahkan.</p>
            @endif
        </div>
        @endforelse

    </div>

    @if($produk->hasPages())
    <div class="mt-8">
        {{ $produk->links() }}
    </div>
    @endif

</div>

</div>

<div class="fixed bottom-8 right-8 z-50">
    <a href="{{ route('produk.create') }}"
        class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
        title="Tambah Produk Baru">
        <i class="fa-solid fa-plus text-2xl"></i>
    </a>
</div>

@endsection