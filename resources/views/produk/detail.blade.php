<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $produk->nama_produk }} - Detail Produk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50">

  <div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Tombol kembali di atas -->
    <div class="mb-4">
      <a href="{{ route('kiosk.index') }}"
         class="text-blue-600 hover:underline text-sm font-medium flex items-center gap-1">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke daftar produk
      </a>
    </div>

    <!-- Breadcrumb -->
    <p class="text-sm text-gray-500 mb-4">Home > {{ $produk->nama_produk }}</p>

    <!-- Gambar produk -->
    @php
      $imgSrc = $produk->gambar
        ? asset('storage/' . ltrim($produk->gambar, '/'))
        : 'https://via.placeholder.com/600x400?text=Gambar+Produk';
    @endphp
    <div class="bg-white rounded-xl overflow-hidden shadow-sm mb-6">
      <img src="{{ $imgSrc }}" alt="{{ $produk->nama_produk }}" class="w-full h-64 object-cover">
    </div>

    <!-- Nama dan harga -->
    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $produk->nama_produk }}</h1>
    <div class="text-xl font-bold text-blue-600 mb-4">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</div>

    <!-- Tombol aksi -->
    <div class="flex items-center gap-4 mb-6">
      <a href="{{ route('kiosk.add', $produk->id_produk) }}"
         class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
        + Keranjang
      </a>
    </div>

    <!-- Deskripsi -->
    <h2 class="text-lg font-bold text-gray-800 mb-2">Deskripsi Produk</h2>
    <p class="text-gray-600 mb-8">{{ $produk->deskripsi_produk }}</p>

    <!-- Rekomendasi Produk -->
    @if(isset($rekomendasi) && count($rekomendasi) > 0)
    <div class="mt-10">
      <h2 class="text-lg font-bold text-gray-800 mb-4">Rekomendasi Produk</h2>

      <div x-data class="relative">
        <!-- Tombol scroll kiri -->
        <button @click="$refs.slider.scrollLeft -= 300"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white shadow px-2 py-2 rounded-full hover:bg-blue-100">
          <i class="fa-solid fa-chevron-left text-blue-600"></i>
        </button>

        <!-- Slider -->
        <div x-ref="slider" class="overflow-x-auto no-scrollbar flex gap-4 px-8 pb-2">
          @foreach($rekomendasi as $item)
          <div class="min-w-[160px] bg-white rounded-xl shadow p-3 flex-shrink-0 hover:shadow-md transition">
            <a href="{{ route('produk.show', $item->id_produk) }}">
              <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_produk }}" class="w-full h-24 object-cover rounded-lg mb-2">
              <h3 class="text-sm font-bold text-gray-800 truncate">{{ $item->nama_produk }}</h3>
              <p class="text-xs text-blue-600 font-semibold">Rp{{ number_format($item->harga_produk, 0, ',', '.') }}</p>
            </a>
            <a href="{{ route('kiosk.add', $item->id_produk) }}"
               class="mt-2 block text-center bg-orange-500 text-white text-xs font-bold px-3 py-2 rounded-lg shadow hover:bg-orange-600 transition">
              + Keranjang
            </a>
          </div>
          @endforeach
        </div>

        <!-- Tombol scroll kanan -->
        <button @click="$refs.slider.scrollLeft += 300"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white shadow px-2 py-2 rounded-full hover:bg-blue-100">
          <i class="fa-solid fa-chevron-right text-blue-600"></i>
        </button>
      </div>
    </div>
    @endif
  </div>

</body>
</html>