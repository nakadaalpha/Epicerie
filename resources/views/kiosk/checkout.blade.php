<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

    <div class="bg-white shadow-sm p-4 sticky top-0 z-10">
        <div class="max-w-md mx-auto flex items-center gap-4">
            <a href="{{ route('kiosk.index') }}" class="text-gray-600"><i class="fa-solid fa-arrow-left text-xl"></i></a>
            <h1 class="text-lg font-bold">Rincian Pesanan</h1>
        </div>
    </div>

    <div class="max-w-md mx-auto p-4 pb-32">
        
        <div class="bg-white rounded-2xl p-4 shadow-sm mb-6">
            <h3 class="text-sm font-bold text-gray-500 mb-3">ITEM DIBELI</h3>
            @foreach($keranjang as $item)
            <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                <div class="flex gap-3 items-center">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-xl">📦</div>
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $item->produk->nama_produk }}</h4>
                        <p class="text-blue-600 text-sm font-medium">Rp{{ number_format($item->produk->harga_produk, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="font-bold text-gray-600">x{{ $item->jumlah }}</div>
            </div>
            @endforeach
        </div>

        <form action="{{ route('kiosk.pay') }}" method="POST">
            @csrf
            
            <h3 class="text-sm font-bold text-gray-500 mb-3">METODE PEMBAYARAN</h3>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
                <label class="flex items-center justify-between p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center"><i class="fa-solid fa-money-bill"></i></div>
                        <span class="font-medium text-gray-700">Tunai</span>
                    </div>
                    <input type="radio" name="metode_pembayaran" value="Tunai" class="w-5 h-5 text-blue-600 focus:ring-blue-500" checked>
                </label>

                <label class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center"><i class="fa-solid fa-qrcode"></i></div>
                        <span class="font-medium text-gray-700">QRIS</span>
                    </div>
                    <input type="radio" name="metode_pembayaran" value="QRIS" class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                </label>
            </div>

            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <div class="max-w-md mx-auto">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-500">Total Bayar</span>
                        <span class="text-2xl font-bold text-blue-700">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold text-lg shadow-lg hover:bg-blue-700 transition flex justify-center items-center gap-2">
                        <i class="fa-solid fa-lock"></i> Bayar Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>

</body>
</html>