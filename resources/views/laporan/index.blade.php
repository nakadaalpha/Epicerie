@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('header_title', 'Laporan Keuangan')

@section('content')
<div class="max-w-7xl mx-auto pb-10">

    {{-- SECTION: Filter & Action --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white">Ringkasan</h2>
            <p class="text-sm text-gray-200">Statistik keuangan toko Anda.</p>
        </div>
        
        <form action="{{ route('laporan.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Dropdown Filter Periode --}}
            <div class="relative">
                <select name="range" onchange="this.form.submit()"
                    class="appearance-none bg-white border border-gray-200 text-gray-700 py-3 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold shadow-sm cursor-pointer">
                    <option value="hari_ini" {{ request('range') == 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="1_minggu" {{ request('range') == '1_minggu' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="1_bulan" {{ request('range') == '1_bulan' ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="6_bulan" {{ request('range') == '6_bulan' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="1_tahun" {{ request('range') == '1_tahun' ? 'selected' : '' }}>1 Tahun Terakhir</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- Tombol Cetak (Opsional) --}}
            <button type="button" onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i> <span class="hidden md:inline">Cetak</span>
            </button>
        </form>
    </div>

    {{-- SECTION: Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] p-6 shadow-lg border border-white/40 flex items-center justify-between group hover:shadow-xl transition relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <i class="fa-solid fa-sack-dollar text-9xl text-blue-600 transform rotate-12"></i>
            </div>
            <div class="relative z-10">
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Pendapatan</p>
                <h2 class="text-3xl md:text-4xl font-black text-gray-800">
                    <span class="text-blue-600 text-lg align-top mr-1">Rp</span>{{ number_format($totalOmzet, 0, ',', '.') }}
                </h2>
                <p class="text-xs text-gray-400 mt-2 font-medium">Periode: {{ $labelPeriode }}</p>
            </div>
            <div class="relative z-10 w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl shadow-sm group-hover:scale-110 transition duration-300">
                <i class="fa-solid fa-dollar"></i>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-6 shadow-lg border border-white/40 flex items-center justify-between group hover:shadow-xl transition relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <i class="fa-solid fa-cart-shopping text-9xl text-green-500 transform -rotate-12"></i>
            </div>
            <div class="relative z-10">
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Transaksi Berhasil</p>
                <h2 class="text-3xl md:text-4xl font-black text-gray-800">
                    {{ $totalTransaksi }} <span class="text-sm text-gray-400 font-medium">Pesanan</span>
                </h2>
                <p class="text-xs text-gray-400 mt-2 font-medium">Periode: {{ $labelPeriode }}</p>
            </div>
            <div class="relative z-10 w-16 h-16 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center text-3xl shadow-sm group-hover:scale-110 transition duration-300">
                <i class="fa-solid fa-check-circle"></i>
            </div>
        </div>
    </div>

    {{-- SECTION: Chart --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl border border-white/40">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <h3 class="text-gray-800 font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-500"></i> Grafik Penjualan
            </h3>
            <span class="text-xs font-bold bg-gray-100 text-gray-500 px-3 py-1 rounded-full">
                {{ $labelPeriode }}
            </span>
        </div>
        
        <div class="h-[400px] w-full relative">
            <canvas id="laporanChart"></canvas>
        </div>
    </div>

</div>

{{-- Chart Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('laporanChart').getContext('2d');
    
    // Data dari Controller
    const labels = @json($laporan->pluck('tanggal'));
    const data = @json($laporan->pluck('total'));

    // Gradient Effect
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.4)'); // Blue-600 opacity
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan',
                data: data,
                borderColor: '#2563eb', // Blue-600
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.3, // Kurva halus
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y;
                            return ' Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
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
                            if(value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                            if(value >= 1000) return 'Rp ' + (value/1000).toFixed(0) + 'rb';
                            return value;
                        },
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
</script>

{{-- Style Print (Agar saat dicetak rapi) --}}
<style>
    @media print {
        body * { visibility: hidden; }
        .max-w-7xl, .max-w-7xl * { visibility: visible; }
        .max-w-7xl { position: absolute; left: 0; top: 0; width: 100%; }
        button, select, form { display: none !important; }
    }
</style>
@endsection