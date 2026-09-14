<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pending - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans p-4">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-4">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-bold text-gray-700">📋 Pesanan Tertunda (Hold)</h2>
            <a href="{{ route('kiosk.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-bold">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if($pendingOrders->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <i class="fa-solid fa-clipboard-check text-4xl mb-2"></i>
                <p>Tidak ada pesanan yang di-hold.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($pendingOrders as $order)
                <div class="border border-gray-200 rounded-lg p-3 hover:bg-blue-50 transition">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-gray-800 text-lg">{{ $order->nama_pelanggan_hold }}</p>
                            <p class="text-xs text-gray-500">
                                <i class="fa-regular fa-clock"></i> {{ $order->tanggal_transaksi }}
                            </p>
                        </div>
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded">
                            Rp {{ number_format($order->total_bayar, 0, ',', '.') }}
                        </span>
                    </div>
                    
                    <a href="{{ route('kiosk.recall', $order->id_transaksi) }}" class="block w-full bg-green-500 text-white text-center py-2 rounded-lg font-bold text-sm shadow hover:bg-green-600 transition">
                        ▶ Lanjutkan Transaksi
                    </a>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</body>
</html>