<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Kustomisasi tampilan select option */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-6xl">

        <div class="mb-8">
            <form action="{{ route('inventaris.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">

                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Produk..."
                        class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/90 backdrop-blur-md border border-white/20">
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
                        class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/90 backdrop-blur-md border border-white/20 cursor-pointer appearance-none">
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
                        class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/90 backdrop-blur-md border border-white/20 cursor-pointer appearance-none">
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

        <div class="bg-white rounded-[2rem] p-8 shadow-2xl min-h-[600px] relative border border-white/40">

            @if(session('success'))
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check text-xl mr-3"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Daftar Inventaris</h2>
                    <p class="text-gray-400 text-sm mt-1">Kelola stok produk toko Anda.</p>
                </div>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                    Total Produk: {{ $produk->total() }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                @forelse($produk as $p)
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl hover:bg-blue-50/50 transition duration-300 border border-gray-100 hover:border-blue-200 hover:shadow-lg hover:-translate-y-1 group relative">

                    <div class="flex items-center w-full overflow-hidden">
                        <div class="relative flex-shrink-0">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-14 h-14 rounded-xl object-cover mr-4 shadow-sm group-hover:scale-105 transition duration-500">
                            @else
                            <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center mr-4 shadow-sm text-xl flex-shrink-0">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            @endif

                            @if($p->stok < 10)
                                <div class="absolute -top-1 -left-1 w-4 h-4 bg-red-500 border-2 border-white rounded-full animate-pulse" title="Stok Kritis">
                        </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-800 capitalize text-base truncate pr-8 group-hover:text-blue-600 transition">{{ $p->nama_produk }}</h3>

                        <div class="flex flex-col text-xs text-gray-500 mt-1.5 space-y-1">
                            <span class="font-extrabold text-blue-600">Rp {{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 px-2 py-0.5 rounded text-[10px] font-bold text-gray-600 border border-gray-200">
                                    {{ $p->kategori->nama_kategori ?? 'Umum' }}
                                </span>
                                <span class="{{ $p->stok < 10 ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                                    Stok: {{ $p->stok }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 absolute right-3 top-1/2 -translate-y-1/2 translate-x-4 group-hover:translate-x-0">
                    <a href="{{ route('produk.edit', $p->id_produk) }}"
                        class="bg-white text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-yellow-400 hover:text-white border border-gray-100 transition"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </a>

                    <form action="{{ route('produk.destroy', $p->id_produk) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-white text-red-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-red-500 hover:text-white border border-gray-100 transition"
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

        <div class="fixed bottom-8 right-8 z-50">
            <a href="{{ route('produk.create') }}"
                class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
                title="Tambah Produk Baru">
                <i class="fa-solid fa-plus text-2xl"></i>
            </a>
        </div>

    </div>
    </div>

</body>

</html>