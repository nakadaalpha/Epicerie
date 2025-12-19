<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Produk - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        details>summary {
            list-style: none;
        }

        details>summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>

<body class="text-gray-700 font-sans">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1280px] mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">

        <aside class="w-full lg:w-[280px] shrink-0">
            <form action="{{ route('kiosk.index') }}" method="GET">
                <input type="hidden" name="search" value="{{ $keyword }}">

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm sticky top-24">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-extrabold text-lg text-gray-800">Filter</h2>
                        <a href="{{ route('kiosk.index') }}" class="text-xs text-blue-600 font-bold hover:underline">Reset</a>
                    </div>

                    <div class="border-b border-gray-100 py-4">
                        <details open class="group">
                            <summary class="flex justify-between items-center font-bold text-gray-800 cursor-pointer">
                                <span>Kategori</span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                                @foreach($allCategories as $cat)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-1 rounded transition">
                                    <input type="checkbox" name="kategori[]" value="{{ $cat->id_kategori }}"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        {{ in_array($cat->id_kategori, $selectedKategori) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-600 flex-1">{{ $cat->nama_kategori }}</span>
                                </label>
                                @endforeach
                            </div>
                        </details>
                    </div>

                    <div class="py-4">
                        <details open class="group">
                            <summary class="flex justify-between items-center font-bold text-gray-800 cursor-pointer">
                                <span>Harga</span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="mt-3 space-y-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs bg-gray-50 px-1 rounded">Rp</span>
                                    <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Minimum" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs bg-gray-50 px-1 rounded">Rp</span>
                                    <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Maksimum" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none">
                                </div>
                                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg text-sm hover:bg-blue-700 transition mt-2 shadow-md">Terapkan</button>
                            </div>
                        </details>
                    </div>
                </div>
            </form>
        </aside>

        <div class="flex-1">

            <div class="mb-6">
                @php
                // 1. Ambil Nama Kategori yang Dipilih dari $allCategories
                $namaKategori = $allCategories->whereIn('id_kategori', $selectedKategori)
                ->pluck('nama_kategori')
                ->toArray();
                $stringKategori = implode(', ', $namaKategori);

                // 2. Tentukan Teks Judul
                if ($keyword && !empty($namaKategori)) {
                // Kasus: Ada Keyword DAN Ada Kategori
                $judul = 'Hasil pencarian "' . $keyword . '"';
                $subJudul = 'di kategori ' . $stringKategori;
                } elseif ($keyword) {
                // Kasus: Cuma Keyword
                $judul = 'Hasil pencarian "' . $keyword . '"';
                $subJudul = '';
                } elseif (!empty($namaKategori)) {
                // Kasus: Cuma Filter Kategori (Ini solusi masalah Anda)
                $judul = $stringKategori;
                $subJudul = '';
                } else {
                // Kasus: Tidak ada filter sama sekali
                $judul = 'Semua Produk';
                $subJudul = '';
                }
                @endphp

                <h1 class="text-xl font-bold text-gray-800">
                    <span class="text-blue-600">{{ $judul }}</span>
                </h1>

                @if($subJudul)
                <p class="text-gray-500 font-medium text-sm mt-1">{{ $subJudul }}</p>
                @endif

                <p class="text-sm text-gray-400 mt-1">Menampilkan {{ count($produk) }} produk</p>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif

            @if($produk->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-gray-200 border-dashed">
                <i class="fa-solid fa-box-open text-6xl text-gray-200 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-500">Produk tidak ditemukan</h3>
                <p class="text-gray-400 text-sm">Coba kata kunci lain atau reset filter.</p>
            </div>
            @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($produk as $p)
                <div class="bg-white p-3 rounded-2xl shadow-sm border flex flex-col justify-between transition-all hover:shadow-md relative group">

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                        <div class="aspect-square rounded-xl mb-2 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>
                        <h3 class="text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>
                        <span class="font-bold text-sm mb-1 block">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                    </a>

                    <div class="flex justify-end mt-2 z-20 relative">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}"
                            class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @else
                        <span class="text-xs text-red-500 font-bold mb-1 py-1">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</body>

</html>