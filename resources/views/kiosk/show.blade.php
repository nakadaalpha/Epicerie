<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $produk->nama_produk }} - Detail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hide-scroll::-webkit-scrollbar {
            display: none;
        }

        .hide-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sticky-card {
            position: sticky;
            top: 100px;
        }
    </style>
</head>

<body class="bg-white font-sans text-gray-800 min-h-screen flex flex-col">

    @include('partials.navbar-kiosk')

    <main class="flex-grow max-w-[1200px] mx-auto w-full px-4 py-6">

        <nav class="flex items-center gap-2 text-sm text-gray-500 mt-5 mb-1 overflow-x-auto whitespace-nowrap pb-2 border-b border-transparent">

            <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold hover:text-blue-800 transition">
                Beranda
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>

            @if($produk->kategori)
            <a href="{{ route('kiosk.index', ['kategori' => $produk->id_kategori]) }}" class="text-blue-600 font-bold hover:text-blue-800 transition">
                {{ $produk->kategori->nama_kategori }}
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @endif

            <span class="text-gray-600 truncate font-medium max-w-[200px] md:max-w-md cursor-default" title="{{ $produk->nama_produk }}">
                {{ $produk->nama_produk }}
            </span>

        </nav>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 relative">

            <div class="lg:col-span-4">
                <div class="sticky top-24">
                    <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200 mb-4 cursor-zoom-in relative group shadow-sm">
                        @if($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-contain p-4 transition duration-500 group-hover:scale-105">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-blue-100">
                            <i class="fa-solid fa-image text-6xl"></i>
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3 overflow-x-auto">
                        <div class="w-16 h-16 border-2 border-blue-600 rounded-lg p-1 bg-white cursor-pointer overflow-hidden">
                            @if($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover rounded">
                            @endif
                        </div>
                        <div class="w-16 h-16 border border-gray-200 rounded-lg bg-gray-50 hover:border-blue-300 transition"></div>
                        <div class="w-16 h-16 border border-gray-200 rounded-lg bg-gray-50 hover:border-blue-300 transition"></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 leading-snug mb-2">{{ $produk->nama_produk }}</h1>

                    <div class="flex items-center gap-2 text-sm mb-4">
                        <span class="font-bold text-gray-900">Terjual 100+</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-yellow-500 font-bold"><i class="fa-solid fa-star"></i> 4.9</span>
                        <span class="text-gray-400">(25 rating)</span>
                    </div>

                    <h2 class="text-3xl font-extrabold text-blue-600">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</h2>
                </div>

                <hr class="border-gray-100 my-2">

                <div class="border-b border-gray-200">
                    <div class="flex gap-6">
                        <button class="text-blue-600 border-b-2 border-blue-600 pb-3 font-bold text-sm">Detail Produk</button>
                        <button class="text-gray-500 pb-3 font-medium text-sm hover:text-blue-600">Info Penting</button>
                    </div>
                </div>

                <div class="py-4 space-y-4">
                    <div class="text-sm text-gray-600">
                        <p class="mb-2"><span class="text-gray-400 w-24 inline-block">Kondisi:</span> <span class="font-bold text-gray-800">Baru</span></p>

                        <p class="mb-2"><span class="text-gray-400 w-24 inline-block">Kategori:</span>
                            <a href="{{ route('kiosk.index', ['kategori' => $produk->id_kategori]) }}" class="text-blue-600 font-bold hover:underline">{{ $produk->kategori->nama_kategori ?? 'Umum' }}</a>
                        </p>

                        <p class="mb-2"><span class="text-gray-400 w-24 inline-block">Etalase:</span> <span class="font-bold text-blue-600">Semua Produk</span></p>
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                        <p>{{ $produk->deskripsi ?? 'Tidak ada deskripsi detail.' }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="sticky-card bg-white border border-gray-200 rounded-xl shadow-xl p-5">
                    <h3 class="font-bold text-gray-900 mb-4">Atur jumlah dan catatan</h3>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center border border-gray-300 rounded-lg p-1">
                            <button class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 font-bold transition">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input type="text" value="1" class="w-10 text-center font-bold text-gray-700 outline-none text-sm" readonly>
                            <button class="w-7 h-7 flex items-center justify-center text-blue-600 hover:text-blue-700 font-bold transition">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">Stok: <span class="font-bold text-gray-800">{{ $produk->stok }}</span></span>
                    </div>

                    <div class="flex justify-between items-center mb-5">
                        <span class="text-gray-500 text-sm">Subtotal</span>
                        <span class="font-bold text-lg text-gray-900">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</span>
                    </div>

                    @if($produk->stok > 0)
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('kiosk.add', $produk->id_produk) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-center transition shadow-lg hover:shadow-blue-500/30">
                            <i class="fa-solid fa-plus mr-2"></i> Keranjang
                        </a>
                        <button class="block w-full border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-3 rounded-lg transition">
                            Beli Langsung
                        </button>
                    </div>
                    @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed">
                        Stok Habis
                    </button>
                    @endif
                </div>
            </div>

        </div>

        @if(isset($produkLain) && count($produkLain) > 0)
        <div class="mt-16 border-t border-gray-100 pt-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Pilihan lainnya untukmu</h3>
                <a href="#" class="text-blue-600 font-bold text-sm hover:text-blue-700">Lihat Semua</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($produkLain as $rek)
                <a href="{{ route('produk.show', $rek->id_produk) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-xl hover:border-blue-500 transition duration-300 group flex flex-col justify-between h-full relative">

                    @if(rand(0,1))
                    <div class="absolute top-0 left-0 bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-br-lg z-10">
                        {{ rand(5,20) }}% OFF
                    </div>
                    @endif

                    <div>
                        <div class="aspect-square flex items-center justify-center overflow-hidden">
                            @if($rek->gambar)
                            <img src="{{ asset('storage/' . $rek->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>

                        <div class="p-3">
                            <h4 class="font-medium text-gray-700 text-sm leading-snug mb-1 line-clamp-2 h-10">{{ $rek->nama_produk }}</h4>
                            <p class="text-gray-900 font-extrabold text-base mb-1">Rp{{ number_format($rek->harga_produk, 0, ',', '.') }}</p>

                            <div class="flex items-center gap-1 text-[10px] text-gray-400 mb-1">
                                <i class="fa-solid fa-location-dot"></i> Kota Jakarta
                            </div>
                            <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                <i class="fa-solid fa-star text-yellow-400"></i>
                                <span class="text-gray-700 font-bold">4.{{ rand(5,9) }}</span>
                                <span class="text-gray-400">| Terjual {{ rand(10, 500) }}+</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-3 mt-2">
                        <div class="block w-full border border-blue-600 text-blue-600 group-hover:bg-blue-600 group-hover:text-white text-xs font-bold py-1.5 rounded-lg text-center transition">
                            + Keranjang
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </main>

    <footer class="mt-20 border-t border-gray-100 py-10 bg-white">
        <div class="max-w-[1200px] mx-auto px-4 text-center text-gray-400 text-sm">
            &copy; 2025 Épicerie Kiosk System. All rights reserved.
        </div>
    </footer>

</body>

</html>