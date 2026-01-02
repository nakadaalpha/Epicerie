<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Épicerie Kiosk</title>
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
    </style>
</head>

<body class="font-sans">

    @include('partials.navbar-kiosk')

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl relative shadow-sm">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
        </div>
    </div>
    @endif
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl relative shadow-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 mt-6 space-y-8">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 text-white w-8 h-8 rounded-lg flex items-center justify-center shadow-blue-200 shadow-md">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Produk Terbaru</h2>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto hide-scroll pb-4 snap-x">
                @foreach($produkTerbaru as $p)
                @php
                $qty = $keranjangItems[$p->id_produk] ?? 0;
                $hasDiskon = $p->persen_diskon > 0;
                @endphp

                <div class="min-w-[170px] w-[170px] bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md relative group snap-center ">

                    <div class="absolute top-2 left-2 z-10 bg-blue-600 text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-bolt"></i> NEW
                    </div>

                    @if($hasDiskon)
                    <div class="absolute top-2 right-2 z-10 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm">
                        -{{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                        <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-xs leading-tight mb-1 line-clamp-2 h-8">{{ $p->nama_produk }}</h3>

                        @if($hasDiskon)
                        <div class="flex flex-col items-start mb-1">
                            <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            <span class="text-blue-600 font-extrabold text-sm block">
                                Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                            </span>
                        </div>
                        @else
                        <span class="text-blue-600 font-extrabold text-sm mb-1 block">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        @endif
                    </a>

                    <div class="flex justify-end mt-2 z-20 relative">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @else
                        <span class="text-xs text-red-500 font-bold mb-1 py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="bg-orange-500 text-white w-8 h-8 rounded-lg flex items-center justify-center shadow-orange-200 shadow-md">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Paling Laris</h2>
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto hide-scroll pb-4 snap-x">
                @foreach($produkTerlaris as $index => $p)
                @php
                $qty = $keranjangItems[$p->id_produk] ?? 0;
                $hasDiskon = $p->persen_diskon > 0;
                @endphp

                <div class="min-w-[170px] w-[170px] bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md relative group snap-center overflow-hidden">

                    <span class="absolute -left-2 -bottom-4 text-7xl font-black text-gray-100 italic select-none pointer-events-none z-0">#{{ $index + 1 }}</span>

                    @if($hasDiskon)
                    <div class="absolute top-2 right-2 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm">
                        -{{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer relative z-10">
                        <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-xs leading-tight mb-1 line-clamp-2 h-8">{{ $p->nama_produk }}</h3>

                        @if($hasDiskon)
                        <div class="flex flex-col items-start mb-1">
                            <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            <span class="text-orange-600 font-extrabold text-sm block">
                                Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                            </span>
                        </div>
                        @else
                        <span class="text-orange-600 font-extrabold text-sm mb-1 block">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        @endif
                    </a>

                    <div class="flex justify-end mt-2 z-20 relative">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-orange-500 hover:bg-orange-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @else
                        <span class="text-xs text-red-500 font-bold py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 pb-24 mt-8">
        <div class="flex justify-between items-center mb-4 sticky top-[70px] backdrop-blur-sm py-3 z-30">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="font-extrabold text-gray-700 text-lg">Semua Produk</h2>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
            @forelse($produk as $p)
            @php
            $qty = $keranjangItems[$p->id_produk] ?? 0;
            // Akses accessor model Produk
            $hasDiskon = $p->persen_diskon > 0;
            @endphp

            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md hover:border-blue-200 relative group">

                @if($hasDiskon)
                <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                    <i class="fa-solid fa-tags"></i> Hemat {{ $p->persen_diskon }}%
                </div>
                @endif

                <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                    <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                        @else
                        <span class="text-4xl">📦</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>

                    @if($hasDiskon)
                    <div class="flex flex-col items-start mb-1">
                        <span class="text-xs text-gray-400 line-through decoration-red-400">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        <span class="text-blue-600 font-bold text-sm block">
                            Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                        </span>
                    </div>
                    @else
                    <span class="text-blue-600 font-bold text-sm mb-1 block">
                        Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                    </span>
                    @endif
                </a>

                <div class="flex justify-end mt-2 z-20 relative">
                    @if($p->stok > 0)
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    @else
                    <span class="text-xs text-red-500 font-bold mb-1 py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-bold">Produk tidak ditemukan.</p>
                <p class="text-xs text-gray-400">Coba kata kunci lain atau reset filter.</p>
            </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            var scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        });

        window.onbeforeunload = function(e) {
            sessionStorage.setItem('scrollpos', window.scrollY);
        };

        function closeQtyModal() {
            document.getElementById('modalQty').classList.add('hidden');
        }
    </script>

</body>

</html>