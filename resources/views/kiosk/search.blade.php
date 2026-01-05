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

<body class=" text-gray-700 font-sans">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1280px] mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">

        <aside class="w-full lg:w-[280px] shrink-0">
            <form action="{{ route('kiosk.search') }}" method="GET">
                <input type="hidden" name="search" value="{{ $keyword }}">

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm sticky top-24">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-extrabold text-lg text-gray-800">Filter</h2>
                        <a href="{{ route('kiosk.search') }}" class="text-xs text-blue-600 font-bold hover:underline">Reset</a>
                    </div>

                    <div class="border-b border-gray-100 py-4">
                        <details open class="group">
                            <summary class="flex justify-between items-center font-bold text-gray-800 cursor-pointer">
                                <span>Kategori</span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
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
                                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg text-sm hover:bg-blue-700 transition mt-2 shadow-md">Terapkan Filter</button>
                            </div>
                        </details>
                    </div>
                </div>
            </form>
        </aside>

        <div class="flex-1">

            <div class="mb-6">
                @php
                $namaKategori = $allCategories->whereIn('id_kategori', $selectedKategori)->pluck('nama_kategori')->toArray();
                $stringKategori = implode(', ', $namaKategori);

                if ($keyword && !empty($namaKategori)) {
                $judul = 'Hasil pencarian "' . $keyword . '"';
                $subJudul = 'di kategori ' . $stringKategori;
                } elseif ($keyword) {
                $judul = 'Hasil pencarian "' . $keyword . '"';
                $subJudul = '';
                } elseif (!empty($namaKategori)) {
                $judul = 'Kategori: ' . $stringKategori;
                $subJudul = '';
                } else {
                $judul = 'Semua Produk';
                $subJudul = '';
                }
                @endphp

                <h1 class="text-2xl font-extrabold text-gray-800">
                    {{ $judul }}
                </h1>

                @if($subJudul)
                <p class="text-gray-500 font-medium text-sm mt-1">{{ $subJudul }}</p>
                @endif

                <p class="text-sm text-gray-400 mt-2">Menampilkan <strong>{{ count($produk) }}</strong> produk</p>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif

            @if($produk->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-gray-200 border-dashed text-center px-4">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-3xl text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-600">Produk tidak ditemukan</h3>
                <p class="text-gray-400 text-sm mt-1">Coba kurangi filter atau gunakan kata kunci lain.</p>
                <a href="{{ route('kiosk.search') }}" class="mt-4 text-blue-600 font-bold hover:underline text-sm">Lihat Semua Produk</a>
            </div>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($produk as $p)
                @php
                $hasDiskon = $p->persen_diskon > 0;
                // Hitung manual harga final jika di model belum ada accessor 'harga_final'
                $hargaFinal = $hasDiskon
                ? $p->harga_produk - ($p->harga_produk * ($p->persen_diskon / 100))
                : $p->harga_produk;
                @endphp

                <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between h-full transition-all hover:shadow-md hover:border-blue-200 relative group overflow-hidden">

                    @if($hasDiskon)
                    <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-tags"></i> Hemat {{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="flex-1 flex flex-col">
                        <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                            @else
                            <i class="fa-solid fa-box text-gray-300 text-3xl"></i>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 min-h-[2.5em]">
                            {{ $p->nama_produk }}
                        </h3>

                        <div class="mt-auto">
                            @if($hasDiskon)
                            <div class="flex flex-col items-start">
                                <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                    Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                                </span>
                                <span class="text-blue-600 font-extrabold text-sm block">
                                    Rp{{ number_format($hargaFinal, 0, ',', '.') }}
                                </span>
                            </div>
                            @else
                            <span class="text-blue-600 font-extrabold text-sm block">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            @endif
                        </div>
                    </a>

                    <div class="flex justify-end mt-3 pt-3 border-t border-gray-50">
                        @if($p->stok > 0)
                        <form action="{{ route('kiosk.add', $p->id_produk) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </form>
                        @else
                        <span class="text-[10px] text-red-500 font-bold py-1 bg-red-50 px-2 rounded-lg border border-red-100">Stok Habis</span>
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