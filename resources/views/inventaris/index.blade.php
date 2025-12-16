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
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-5xl">

        <div class="mb-6 relative">
            <form action="{{ route('inventaris') }}" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Produk..."
                    class="w-full p-3 pl-5 rounded-full shadow-lg outline-none focus:ring-2 focus:ring-blue-300 text-gray-600 transition">
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

            <div class="flex justify-between items-center mb-6 ml-2">
                <h2 class="text-blue-500 font-bold text-xl">Daftar Inventaris</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-md">Total: {{ $produk->count() }}</span>
            </div>

            <div class="space-y-5">
                @foreach($produk as $index => $p)
                <div class="group relative">

                    <div class="flex justify-between items-end px-3 mb-1">
                        <div class="flex items-center text-gray-700 font-semibold">
                            <span class="mr-3 text-gray-400 text-sm font-bold">{{ $index + 1 }}.</span>
                            <span class="text-lg capitalize">{{ $p->nama_produk }}</span>
                        </div>
                        <span class="text-gray-400 text-xs uppercase font-bold tracking-wider bg-gray-100 px-2 py-1 rounded">
                            {{ $p->kategori->nama_kategori ?? 'UMUM' }}
                        </span>
                    </div>

                    <div class="bg-[#3b4bbd] text-white py-3 px-4 rounded-xl shadow-md relative group-hover:bg-[#2f3c9e] transition-all duration-300 transform group-hover:-translate-y-1">

                        <div class="flex justify-between items-center">

                            <span class="text-sm font-medium tracking-wide">
                                Rp {{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>

                            <div class="flex items-center text-sm font-bold mr-6">
                                @if($p->stok < 10)
                                    <i class="fa-solid fa-triangle-exclamation text-yellow-300 mr-2 animate-pulse" title="Stok Menipis!"></i>
                                    @endif
                                    {{ $p->stok }} Unit
                            </div>

                            <i class="fa-solid fa-chevron-right text-xs opacity-50 group-hover:hidden absolute right-4"></i>
                        </div>

                        <div class="absolute inset-y-0 right-2 flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <a href="{{ route('produk.edit', $p->id_produk) }}"
                                class="bg-white text-yellow-500 w-8 h-8 rounded-lg flex items-center justify-center shadow hover:bg-yellow-100 hover:scale-110 transition"
                                title="Edit Produk">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>

                            <a href="{{ route('produk.hapus', $p->id_produk) }}"
                                onclick="return confirm('Hapus produk {{ $p->nama_produk }}?')"
                                class="bg-white text-red-500 w-8 h-8 rounded-lg flex items-center justify-center shadow hover:bg-red-100 hover:scale-110 transition"
                                title="Hapus Produk">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </a>
                        </div>
                    </div>

                    @php $persen = min(($p->stok / ($maxStock > 0 ? $maxStock : 1)) * 100, 100); @endphp
                    <div class="w-full bg-gray-100 rounded-full h-1 mt-1 overflow-hidden opacity-0 group-hover:opacity-100 transition duration-500">
                        <div class="bg-blue-400 h-1 rounded-full shadow-sm" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
                @endforeach

                @if($produk->isEmpty())
                <div class="flex flex-col items-center justify-center text-gray-400 py-20">
                    <div class="bg-gray-100 p-6 rounded-full mb-4">
                        <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                    </div>
                    <p class="font-medium">Inventaris kosong.</p>
                    <p class="text-sm opacity-60">Belum ada produk yang ditambahkan.</p>
                </div>
                @endif
            </div>

            <div class="absolute bottom-8 right-8 flex flex-col space-y-4 z-10">
                <button class="bg-gray-200 text-gray-500 w-10 h-10 rounded-full shadow hover:bg-gray-300 flex items-center justify-center transition opacity-0 group-hover:opacity-100 transform translate-y-full group-hover:translate-y-0 duration-300">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <a href="{{ route('produk.create') }}"
                    class="bg-[#3b4bbd] text-white w-14 h-14 rounded-full shadow-xl hover:bg-blue-800 flex items-center justify-center transform hover:scale-110 hover:rotate-90 transition duration-300 z-20"
                    title="Tambah Produk">
                    <i class="fa-solid fa-plus text-2xl"></i>
                </a>

            </div>

        </div>
    </div>

</body>

</html>