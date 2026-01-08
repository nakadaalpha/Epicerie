<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-blue-500 to-teal-400 min-h-screen text-gray-800">

    @include('partials.navbar')

    <div class="pt-10 px-6 pb-10">
        <div class="container mx-auto max-w-6xl">
            <div class="flex flex-col md:flex-row justify-between items-center text-white mb-6">
                <div>
                    <h1 class="text-lg opacity-90 font-medium">Selamat Datang kembali,</h1>
                    <h2 class="text-3xl font-extrabold tracking-tight drop-shadow-md">{{ Auth::user()->nama ?? 'Admin' }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-6 max-w-6xl -mt-6">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/95 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-white/20 flex flex-col justify-between">
                <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Produk</div>
                <div class="flex justify-between items-end">
                    <span class="text-2xl font-black text-gray-800">{{ $totalProduk }}</span>
                    <i class="fa-solid fa-box text-blue-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white/95 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-white/20 flex flex-col justify-between">
                <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pelanggan</div>
                <div class="flex justify-between items-end">
                    <span class="text-2xl font-black text-gray-800">{{ $totalUser }}</span>
                    <i class="fa-solid fa-users text-teal-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white/95 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-white/20 flex flex-col justify-between">
                <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Order Hari Ini</div>
                <div class="flex justify-between items-end">
                    <span class="text-2xl font-black text-blue-600">{{ $totalTransaksiHariIni }}</span>
                    <i class="fa-solid fa-receipt text-blue-200 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white/95 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-white/20 flex flex-col justify-between">
                <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Omzet Hari Ini</div>
                <div class="flex justify-between items-end">
                    <span class="text-lg font-black text-teal-600">Rp{{ number_format($omzetHariIni, 0, ',', '.') }}</span>
                    <i class="fa-solid fa-coins text-yellow-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Grafik Pendapatan</h3>
                            <p class="text-xs text-gray-400">7 Hari Terakhir</p>
                        </div>
                        <div class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                            <i class="fa-solid fa-arrow-trend-up"></i> Realtime
                        </div>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-crown text-yellow-500 mr-2"></i> Paling Laris
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Produk</th>
                                    <th class="px-4 py-3 text-center">Terjual</th>
                                    <th class="px-4 py-3 text-right rounded-r-lg">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produkTerlaris as $index => $item)
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-900 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                            #{{ $index + 1 }}
                                        </div>
                                        <span class="truncate max-w-[150px]">{{ $item->nama_produk }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded">{{ $item->total_terjual }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $item->stok <= 15 ? 'text-red-500' : 'text-gray-600' }}">
                                        {{ $item->stok }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-gray-400">Belum ada penjualan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-red-100 rounded-bl-[4rem] -mr-4 -mt-4 opacity-50"></div>

                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i> Stok Menipis
                    </h3>

                    <div class="space-y-3">
                        @forelse($stokHampirHabis as $item)
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl hover:bg-red-50 transition border border-gray-100 hover:border-red-100 group">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse shrink-0"></div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-700 text-sm truncate max-w-[100px]">{{ $item->nama_produk }}</span>
                                    <span class="text-[10px] text-gray-400">Sisa: <span class="text-red-600 font-bold">{{ $item->stok }}</span></span>
                                </div>
                            </div>
                            <a href="{{ route('inventaris.produk.edit', $item->id_produk) }}" class="text-xs bg-white border border-gray-200 text-gray-600 hover:border-red-500 hover:text-red-600 px-3 py-1.5 rounded-lg font-bold transition shadow-sm">
                                + Isi
                            </a>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-400 text-xs">Stok aman.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/30 rounded-3xl p-6 shadow-xl text-white">
                    <h3 class="font-bold text-lg mb-1 drop-shadow-md">Aksi Cepat</h3>
                    <p class="text-blue-100 text-xs mb-4">Shortcut pengelolaan.</p>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('kategori.index') }}" class="bg-white/20 hover:bg-white/30 border border-white/20 p-3 rounded-xl flex flex-col items-center justify-center text-center transition cursor-pointer text-white no-underline shadow-sm">
                            <i class="fa-solid fa-tags text-xl mb-1"></i>
                            <span class="text-xs font-bold">Kategori</span>
                        </a>
                        <a href="{{ route('slider.index') }}" class="bg-white/20 hover:bg-white/30 border border-white/20 p-3 rounded-xl flex flex-col items-center justify-center text-center transition cursor-pointer text-white no-underline shadow-sm">
                            <i class="fa-regular fa-images text-xl mb-1"></i>
                            <span class="text-xs font-bold">Slider</span>
                        </a>
                        <a href="{{ route('transaksi.index') }}" class="col-span-2 bg-white text-blue-600 hover:bg-gray-50 p-3 rounded-xl flex items-center justify-center text-center transition cursor-pointer font-bold text-sm shadow-md">
                            <i class="fa-solid fa-file-invoice mr-2"></i> Kelola Pesanan
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');

        // Gradient Biru ke Teal untuk Chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(20, 184, 166, 0.5)'); // Teal-500
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Blue-500 transparent

        const labels = @json($chartLabels);
        const data = @json($chartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: data,
                    borderColor: '#0d9488', // Teal-600
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d9488',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            font: {
                                size: 10
                            },
                            callback: function(value) {
                                return 'Rp ' + (value / 1000) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>