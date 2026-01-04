<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="font-sans">

    @include('partials.navbar-kiosk')

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-red-100 text-red-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Wishlist Saya</h1>
                <p class="text-sm text-gray-500">Barang impian yang kamu simpan.</p>
            </div>
        </div>

        @if($wishlists->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
            <i class="fa-regular fa-heart text-6xl text-gray-200 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-600">Wishlist Kosong</h3>
            <p class="text-gray-400 text-sm mb-4">Yuk cari barang kesukaanmu dulu!</p>
            <a href="{{ route('kiosk.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                Mulai Belanja
            </a>
        </div>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
            @foreach($wishlists as $w)
            @php $p = $w->produk; @endphp

            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative group hover:shadow-md transition">

                <form action="{{ route('wishlist.toggle', $p->id_produk) }}" method="POST" class="absolute top-2 right-2 z-10">
                    @csrf
                    <button type="button" onclick="toggleWishlist(this, '{{ $p->id_produk }}'); this.closest('.group').remove();" class="w-7 h-7 bg-white rounded-full text-red-500 shadow-sm flex items-center justify-center hover:bg-red-50 transition">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </form>

                <a href="{{ route('produk.show', $p->id_produk) }}" class="block">
                    <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden bg-gray-50">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3">
                        @else
                        <span class="text-2xl">📦</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-800 text-xs line-clamp-2 h-8 mb-1">{{ $p->nama_produk }}</h3>
                    <span class="text-blue-600 font-extrabold text-sm">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                </a>

                <div class="mt-3">
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="block w-full bg-blue-600 text-white text-xs font-bold py-2 rounded-lg text-center hover:bg-blue-700 transition">
                        + Keranjang
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <script>
        async function toggleWishlist(btn, productId) {
            try {
                await fetch("/wishlist/toggle/" + productId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                // Logic hapus elemen HTML dilakukan di onclick button di atas agar lebih cepat
            } catch (error) {
                console.error(error);
            }
        }
    </script>
</body>

</html>