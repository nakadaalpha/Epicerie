@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('header_title', 'Laporan Keuangan')

@section('content')
<div class="max-w-6xl mx-auto">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] p-6 shadow-lg border border-white/40 flex items-center justify-between group hover:shadow-xl transition">
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Pendapatan</p>
                <h2 class="text-3xl font-extrabold text-blue-600 mt-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h2>
            </div>
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 shadow-lg border border-white/40 flex items-center justify-between group hover:shadow-xl transition">
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Transaksi Berhasil</p>
                <h2 class="text-3xl font-extrabold text-green-500 mt-1">{{ $totalTransaksi }} <span class="text-sm text-gray-400 font-medium">Pesanan</span></h2>
            </div>
            <div class="w-14 h-14 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                <i class="fa-solid fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] p-8 shadow-2xl border border-white/40">
        <h3 class="text-gray-800 font-bold mb-6 border-b border-gray-100 pb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-blue-500"></i> Grafik Penjualan (7 Hari Terakhir)
        </h3>
        <div class="h-[350px] w-full">
            <canvas id="laporanChart"></canvas>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('laporanChart').getContext('2d');
    const labels = @json($laporan - > pluck('tanggal'));
    const data = @json($laporan - > pluck('total'));

    // Gradient Chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: data,
                borderColor: '#2563eb',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2563eb',
                pointRadius: 5
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
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'k';
                        }
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
@endsection