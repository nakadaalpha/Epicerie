<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-5xl">

        <div class="mb-6 relative">
            <form action="{{ route('kategori.index') }}" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kategori..."
                    class="w-full p-3 pl-5 rounded-full shadow-lg outline-none focus:ring-2 focus:ring-blue-300 text-gray-600 transition bg-white/90 backdrop-blur-sm">
                <button type="submit" class="absolute right-5 top-3.5 text-gray-400 hover:text-blue-500 transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-2xl min-h-[600px] relative">

            @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 flex items-center animate-pulse">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
            @endif

            <div class="flex justify-between items-center mb-6 ml-1">
                <h2 class="text-blue-500 font-bold text-xl">Daftar Kategori</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Total: {{ $kategori->count() }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                @forelse($kategori as $k)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-blue-50 transition duration-300 border border-transparent hover:border-blue-100 group relative">

                    <div class="flex items-center">
                        @if($k->gambar)
                        <img src="{{ asset('storage/' . $k->gambar) }}" class="w-12 h-12 rounded-full object-cover mr-4 border-2 border-white shadow-sm">
                        @else
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-4 font-bold border-2 border-white shadow-sm text-lg">
                            {{ substr($k->nama_kategori, 0, 1) }}
                        </div>
                        @endif

                        <div>
                            <h3 class="font-bold text-gray-800 capitalize text-sm md:text-base">{{ $k->nama_kategori }}</h3>
                            <p class="text-xs text-gray-400 font-medium mt-0.5">
                                {{ $k->produk_count ?? 0 }} Produk
                            </p>
                        </div>
                    </div>

                    <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0 absolute right-4">
                        <a href="{{ route('kategori.edit', $k->id_kategori) }}" class="bg-white text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-yellow-50 transition" title="Edit">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                        <a href="{{ route('kategori.destroy', $k->id_kategori) }}" onclick="return confirm('Hapus kategori ini?')" class="bg-white text-red-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-red-50 transition" title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </a>
                    </div>
                </div>
                @empty
                @endforelse
            </div>

            @if($kategori->isEmpty())
            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 pointer-events-none">
                <div class="bg-gray-100 p-6 rounded-full mb-4 animate-bounce">
                    <i class="fa-solid fa-layer-group text-4xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-500 text-lg">Kategori Kosong.</h3>
                <p class="text-sm opacity-60">Belum ada kategori yang ditambahkan.</p>
            </div>
            @endif

            <div class="absolute bottom-8 right-8 z-10">
                <a href="{{ route('kategori.create') }}"
                    class="bg-[#3b4bbd] text-white w-14 h-14 rounded-full hover:bg-blue-800 flex items-center justify-center transform hover:scale-110 hover:rotate-90 transition duration-300"
                    title="Tambah Kategori Baru">
                    <i class="fa-solid fa-plus text-2xl"></i>
                </a>
            </div>

        </div>
    </div>

</body>

</html>