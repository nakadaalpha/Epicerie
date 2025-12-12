<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-teal-500 font-sans">

    <nav class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <div class="text-xl font-bold tracking-widest">ÈPICERIE</div>
        <div class="flex space-x-6 text-sm">
            <a href="#" class="font-bold border-b-2 border-white pb-1">Dashboard</a>
            <a href="#" class="opacity-80 hover:opacity-100">Inventaris</a>
            <a href="#" class="opacity-80 hover:opacity-100">Laporan</a>
            <a href="#" class="opacity-80 hover:opacity-100">Karyawan</a>
        </div>
        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
    </nav>

    <div class="container mx-auto p-6">

        <div class="text-center text-white mb-8">
            <h1 class="text-2xl">Selamat Datang,</h1>
            <h2 class="text-3xl font-bold">Mas Acheng</h2>
            <div class="flex justify-end mt-2">
                <button class="bg-white text-gray-700 px-4 py-1 rounded-full text-sm flex items-center shadow">
                    🕒 Riwayat Transaksi
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-gray-500 text-sm font-semibold">Laporan Penjualan</h3>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp. {{ number_format($chartData->sum('total'), 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400">Total Periode Ini</p>
                        </div>
                        <select class="text-xs border rounded p-1">
                            <option>Minggu Ini</option>
                        </select>
                    </div>
                    <canvas id="salesChart" height="150"></canvas>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg flex justify-between items-center">
                    <h3 class="text-blue-500 font-semibold text-sm">Total Transaksi Hari Ini</h3>
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-xl">
                        Rp {{ number_format($omzetHariIni, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-blue-500 font-semibold mb-4">Produk Terlaris</h3>
                    <ul class="space-y-2">
                        @foreach($produkTerlaris as $index => $item)
                        <li class="flex justify-between items-center p-2 rounded {{ $index == 0 ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700' }}">
                            <div class="flex items-center">
                                <span class="mr-3 font-bold">{{ $index + 1 }}.</span>
                                <span>{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</span>
                            </div>
                            <span class="text-sm font-semibold">{{ $item->total_terjual }} Unit</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-blue-500 font-semibold mb-4 flex items-center">
                        <span class="mr-2 text-red-500">⚠️</span> Stok Hampir Habis
                    </h3>
                    <ul class="space-y-2">
                        @forelse($stokHampirHabis as $item)
                        <li class="flex justify-between items-center p-3 bg-gray-100 rounded text-gray-700">
                            <div class="flex items-center">
                                <span class="mr-3 font-bold">1.</span>
                                <span>{{ $item->nama_produk }}</span>
                            </div>
                            <span class="text-sm font-bold text-red-600">{{ $item->stok }} Unit</span>
                        </li>
                        @empty
                        <li class="text-gray-400 text-sm italic">Stok aman semua.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');

        // Data dari Controller Laravel
        const labels = @json($chartData->pluck('bulan'));
        const dataValues = @json($chartData->pluck('total'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: dataValues,
                    borderColor: '#6366f1', // Warna ungu/biru
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    tension: 0.4, // Membuat garis melengkung (smooth)
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>