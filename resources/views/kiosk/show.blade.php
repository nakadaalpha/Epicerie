<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - {{ $produk->nama_produk }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row">

        <div class="w-full md:w-1/2 h-96 bg-gray-200 relative">
            @if($produk->gambar)
            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <i class="fa-solid fa-image text-6xl"></i>
            </div>
            @endif

            <a href="{{ route('kiosk.index') }}" class="absolute top-4 left-4 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg transition">
                <i class="fa-solid fa-arrow-left text-gray-800"></i>
            </a>
        </div>

        <div class="w-full md:w-1/2 p-10 flex flex-col justify-between">
            <div>
                <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                    {{ $produk->kategori->nama_kategori ?? 'Umum' }}
                </span>
                <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-2">{{ $produk->nama_produk }}</h1>
                <p class="text-4xl font-bold text-blue-600 my-4">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                <p class="text-gray-500 leading-relaxed mb-6">
                    {{ $produk->deskripsi ?? 'Tidak ada deskripsi untuk produk ini.' }}
                </p>

                <div class="flex items-center text-sm text-gray-500 mb-8">
                    <i class="fa-solid fa-box mr-2"></i> Stok Tersedia:
                    <span class="font-bold text-gray-800 ml-1">{{ $produk->stok }}</span>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('kiosk.add', $produk->id_produk) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-4 rounded-xl shadow-lg transition transform hover:-translate-y-1">
                    <i class="fa-solid fa-cart-plus mr-2"></i> Tambah ke Keranjang
                </a>
            </div>
        </div>
    </div>

</body>

</html>