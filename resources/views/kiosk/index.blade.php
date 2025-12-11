<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Épicerie Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

    <div class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-blue-600">Épicerie</h1>
                <p class="text-xs text-gray-500">Selamat Berbelanja</p>
            </div>
            <a href="{{ route('kiosk.checkout') }}" class="relative p-2 text-gray-600 hover:text-blue-600">
                <i class="fa-solid fa-cart-shopping text-2xl"></i>
                @if($totalItemKeranjang > 0)
                <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                    {{ $totalItemKeranjang }}
                </span>
                @endif
            </a>
        </div>
        <div class="px-4 pb-4 max-w-md mx-auto">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" placeholder="Cari produk..." class="w-full bg-gray-100 rounded-xl py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="max-w-md mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    </div>
    @endif

    <div class="max-w-md mx-auto px-4 pb-24 mt-4">
        <h2 class="font-bold text-gray-700 mb-3">Semua Produk</h2>
        
        <div class="grid grid-cols-2 gap-4">
            @foreach($produk as $p)
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="aspect-square bg-blue-50 rounded-xl mb-3 flex items-center justify-center overflow-hidden">
                    <span class="text-4xl">📦</span>
                </div>
                
                <div>
                    <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1">{{ $p->nama_produk }}</h3>
                    <p class="text-xs text-gray-500 mb-2">{{ $p->deskripsi_produk }}</p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-blue-600 font-bold text-sm">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                        
                        @if($p->stok > 0)
                            <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                                <i class="fa-solid fa-plus"></i>
                            </a>
                        @else
                            <span class="text-xs text-red-500 font-bold">Habis</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if($totalItemKeranjang > 0)
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4">
        <div class="max-w-md mx-auto">
            <a href="{{ route('kiosk.checkout') }}" class="block w-full bg-blue-600 text-white text-center py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">
                Lanjut Pembayaran ({{ $totalItemKeranjang }} Item)
            </a>
        </div>
    </div>
    @endif

</body>
</html>