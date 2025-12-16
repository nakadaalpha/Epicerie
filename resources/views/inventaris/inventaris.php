<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Épicerie Inventaris</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .gradient-bg {
            background: linear-gradient(180deg, #4c6ef5 0%, #22d3ee 100%); /* Biru ke Tosca */
            min-height: 100vh;
        }
    </style>
</head>
<body class="gradient-bg p-8">

    <div class="flex justify-between items-center text-white mb-8 px-4">
        <h1 class="text-2xl font-light tracking-widest">ÉPICERIE</h1>
        <div class="flex gap-8 text-sm font-medium">
            <a href="#" class="opacity-70 hover:opacity-100 flex flex-col items-center gap-1">
                <span>🏠</span> Dashboard
            </a>
            <a href="#" class="opacity-100 border-b-2 border-white pb-1 flex flex-col items-center gap-1">
                <span>📖</span> Inventaris
            </a>
            <a href="#" class="opacity-70 hover:opacity-100 flex flex-col items-center gap-1">
                <span>📄</span> Laporan
            </a>
            <a href="#" class="opacity-70 hover:opacity-100 flex flex-col items-center gap-1">
                <span>👥</span> Karyawan
            </a>
        </div>
        <div class="w-8 h-8 bg-white rounded-full opacity-80"></div> </div>

    <div class="mb-6">
        <input type="text" placeholder="🔍 Cari Barang" 
               class="w-full p-3 rounded-full text-center text-sm shadow-sm focus:outline-none text-gray-600">
    </div>

    <div class="bg-white rounded-[30px] p-8 shadow-xl min-h-[600px] relative">
        
        <h2 class="text-blue-600 text-xs font-bold mb-6 uppercase tracking-wider">Stok Barang</h2>

        <div class="space-y-6">
            @foreach($produk as $index => $item)
            <div class="relative">
                
                <div class="flex justify-between items-end mb-1 text-gray-800">
                    <span class="text-sm font-medium">
                        {{ $index + 1 }}. {{ $item->nama_produk }}
                    </span>
                    <span class="text-xs font-bold">
                        Rp {{ number_format($item->harga_produk, 0, ',', '.') }}
                    </span>
                </div>

                <div class="w-full bg-[#3b5bdb] rounded-r-full rounded-bl-full h-8 flex items-center relative overflow-hidden">
                    
                    @php
                        $barColor = $item->stok < 2100 ? 'bg-yellow-400' : 'bg-green-600';
                        // Menghitung lebar bar dalam persen
                        $width = ($item->stok / $maxStock) * 100;
                    @endphp

                    <div class="h-full {{ $barColor }} rounded-r-full rounded-bl-full flex items-center justify-end pr-3 text-white text-[10px] font-bold shadow-md transition-all duration-500"
                         style="width: {{ $width }}%;">
                         {{ $item->stok }} Unit
                    </div>

                </div>
            </div>
            @endforeach
        </div>

        <div class="fixed bottom-10 right-10 flex flex-col gap-3">
            <button class="bg-[#3b5bdb] text-white w-12 h-12 rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center text-2xl font-light">+</button>
            <button class="bg-gray-300 text-gray-600 w-12 h-12 rounded-full shadow-lg hover:bg-gray-400 flex items-center justify-center text-lg">✎</button>
            <button class="bg-[#3b82f6] text-white w-14 h-14 rounded-full shadow-2xl hover:bg-blue-500 flex items-center justify-center text-xl">📂</button>
        </div>

    </div>

</body>
</html>