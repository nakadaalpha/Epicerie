<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-5xl">

        <div class="text-white mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-xl opacity-90">Selamat Datang,</h1>
                <h2 class="text-3xl font-bold">{{ Auth::user()->nama }}</h2>
            </div>
            
            <a href="{{ route('transaksi.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-full text-sm flex items-center shadow backdrop-blur-sm transition cursor-pointer text-decoration-none">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Riwayat Transaksi
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-gray-500 text-sm font-semibold">Laporan Penjualan</h3>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp {{ number_format($omzetHariIni ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400">Total Hari Ini</p>
                        </div>
                        <select class="text-xs border rounded p-1 bg-gray-50 outline-none">
                            <option>Hari Ini</option>
                        </select>
                    </div>
                    <canvas id="salesChart" height="150"></canvas>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-2xl flex justify-between items-center">
                    <div>
                        <h3 class="text-blue-500 font-semibold text-sm">Jumlah Transaksi</h3>
                        <p class="text-xs text-gray-400">Hari ini</p>
                    </div>
                    <div class="bg-[#3b4bbd] text-white px-6 py-3 rounded-full font-bold text-xl shadow-lg">
                        {{ $totalTransaksiHariIni ?? 0 }} Pesanan
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <h3 class="text-blue-500 font-semibold mb-4 ml-1">Produk Terlaris (Top 4)</h3>
                    <ul class="space-y-3">
                        @forelse($produkTerlaris as $index => $item)
                        <li class="flex justify-between items-center p-3 rounded-2xl {{ $index == 0 ? 'bg-[#3b4bbd] text-white shadow-md' : 'bg-gray-50 text-gray-700' }}">
                            <div class="flex items-center">
                                <span class="mr-3 font-bold w-6 text-center">{{ $index + 1 }}.</span>
                                <span class="font-medium truncate max-w-[150px]">{{ $item->nama_produk ?? 'Produk' }}</span>
                            </div>
                            <span class="text-sm font-bold bg-white/20 px-3 py-1 rounded-full text-xs">Stok: {{ $item->stok }}</span>
                        </li>
                        @empty
                        <li class="text-center text-gray-400 text-sm py-4">Belum ada data penjualan.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl relative overflow-hidden min-h-[300px]">

                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i> Stok Hampir Habis
                    </h3>

                    <div class="space-y-3">
                        @forelse($stokHampirHabis as $item)

                        <a href="{{ route('produk.edit', $item->id_produk) }}"
                            class="group block relative bg-red-50 rounded-xl p-3 border border-red-100 transition-all duration-300 hover:shadow-md hover:bg-red-100 cursor-pointer overflow-hidden">

                            <div class="flex justify-between items-center transition-all duration-300 group-hover:opacity-20 group-hover:blur-[1px]">

                                <div class="flex items-center overflow-hidden mr-2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-3 flex-shrink-0 animate-pulse"></div>
                                    <span class="font-bold text-gray-700 text-sm truncate">
                                        {{ $item->nama_produk }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-red-600 font-bold text-sm bg-white px-3 py-1.5 rounded-lg shadow-sm border border-red-100 whitespace-nowrap">
                                        {{ $item->stok }} Unit
                                    </span>
                                </div>
                            </div>

                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
                                <div class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg flex items-center gap-2 transform scale-90 group-hover:scale-100 transition-transform">
                                    <i class="fa-solid fa-plus"></i> Tambah Stok
                                </div>
                            </div>

                        </a>

                        @empty
                        <div class="flex flex-col items-center justify-center h-40 text-gray-400 opacity-60">
                            <i class="fa-solid fa-check-circle text-4xl text-green-300 mb-2"></i>
                            <p class="text-sm">Stok aman terkendali.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // SAYA BUAT DATA DUMMY DULU AGAR TIDAK ERROR (Karena controller belum kirim $chartData)
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Penjualan',
                    data: [12, 19, 3, 5, 2, 3, 10], // Data Dummy Sementara
                    borderColor: '#3b4bbd',
                    backgroundColor: 'rgba(59, 75, 189, 0.1)',
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b4bbd',
                    pointBorderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', drawBorder: false },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    </script>
</body>

</html>