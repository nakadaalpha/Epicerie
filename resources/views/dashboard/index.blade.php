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
                <h2 class="text-3xl font-bold">Mas Acheng</h2>
            </div>
            <button class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-full text-sm flex items-center shadow backdrop-blur-sm transition">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Riwayat Transaksi
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-gray-500 text-sm font-semibold">Laporan Penjualan</h3>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp. {{ number_format($chartData->sum('total'), 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400">Total Periode Ini</p>
                        </div>
                        <select class="text-xs border rounded p-1 bg-gray-50 outline-none">
                            <option>Minggu Ini</option>
                        </select>
                    </div>
                    <canvas id="salesChart" height="150"></canvas>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-2xl flex justify-between items-center">
                    <div>
                        <h3 class="text-blue-500 font-semibold text-sm">Total Transaksi</h3>
                        <p class="text-xs text-gray-400">Hari ini</p>
                    </div>
                    <div class="bg-[#3b4bbd] text-white px-6 py-3 rounded-full font-bold text-xl shadow-lg">
                        Rp {{ number_format($omzetHariIni, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <h3 class="text-blue-500 font-semibold mb-4 ml-1">Produk Terlaris</h3>
                    <ul class="space-y-3">
                        @foreach($produkTerlaris as $index => $item)
                        <li class="flex justify-between items-center p-3 rounded-2xl {{ $index == 0 ? 'bg-[#3b4bbd] text-white shadow-md' : 'bg-gray-50 text-gray-700' }}">
                            <div class="flex items-center">
                                <span class="mr-3 font-bold w-6 text-center">{{ $index + 1 }}.</span>
                                <span class="font-medium">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</span>
                            </div>
                            <span class="text-sm font-bold bg-white/20 px-3 py-1 rounded-full">{{ $item->total_terjual }} Unit</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl relative overflow-hidden min-h-[300px]">

                    <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i> Stok Hampir Habis
                    </h3>

                    <div class="space-y-3">
                        @forelse($stokHampirHabis as $item)

                        <div class="group bg-red-50 rounded-xl p-3 border border-red-100 transition-all duration-300 hover:shadow-md hover:bg-red-100">

                            <div class="flex justify-between items-center">

                                <div class="flex items-center overflow-hidden mr-2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-3 flex-shrink-0 animate-pulse"></div>
                                    <span class="font-bold text-gray-700 text-sm truncate">
                                        {{ $item->nama_produk }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">

                                    <span class="text-red-600 font-bold text-sm bg-white px-3 py-1.5 rounded-lg shadow-sm border border-red-100 whitespace-nowrap z-10">
                                        {{ $item->stok }} Unit
                                    </span>

                                    <a href="{{ route('produk.edit', $item->id_produk) }}"
                                        class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-md 
                              opacity-0 -translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 
                              transition-all duration-300 ease-out"
                                        title="Tambah Stok">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </a>

                                </div>
                            </div>

                        </div>

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
        const ctx = document.getElementById('salesChart').getContext('2d');
        const labels = @json($chartData->pluck('bulan'));
        const dataValues = @json($chartData->pluck('total'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: dataValues,
                    borderColor: '#3b4bbd', // Sesuaikan warna dengan tema Karyawan
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
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 10
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