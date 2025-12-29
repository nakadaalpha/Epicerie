<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap'); body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-6xl">

        <div class="text-white mb-8">
            <h1 class="text-3xl font-bold">Laporan Penjualan</h1>
            <p class="opacity-80">Analisis performa penjualan toko Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-bold uppercase">Total Pendapatan (Selesai)</p>
                    <h2 class="text-3xl font-bold text-blue-600 mt-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h2>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-bold uppercase">Total Transaksi Berhasil</p>
                    <h2 class="text-3xl font-bold text-green-600 mt-1">{{ $totalTransaksi }} <span class="text-sm text-gray-400 font-normal">Pesanan</span></h2>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-xl">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-2xl">
            <h3 class="text-gray-700 font-bold mb-6 border-b border-gray-100 pb-4">
                <i class="fa-solid fa-chart-line text-blue-500 mr-2"></i> Grafik Penjualan (7 Hari Terakhir)
            </h3>
            <div class="h-[300px] w-full">
                <canvas id="laporanChart"></canvas>
            </div>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('laporanChart').getContext('2d');
        
        // Data dari Controller
        const labels = @json($laporan->pluck('tanggal'));
        const data = @json($laporan->pluck('total'));

        new Chart(ctx, {
            type: 'bar', // Bisa ganti 'line' kalau mau garis
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: data,
                    backgroundColor: '#3b82f6',
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

</body>
</html>