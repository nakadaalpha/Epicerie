@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Ringkasan Toko')

@section('content')

{{-- Style Custom untuk Scrollbar Modal --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>

<div class="max-w-7xl mx-auto">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/60 flex flex-col justify-between hover:shadow-md transition">
            <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Produk</div>
            <div class="flex justify-between items-end">
                <span class="text-3xl font-black text-gray-800">{{ $totalProduk }}</span>
                <i class="fa-solid fa-box text-blue-200 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/60 flex flex-col justify-between hover:shadow-md transition">
            <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pelanggan</div>
            <div class="flex justify-between items-end">
                <span class="text-3xl font-black text-gray-800">{{ $totalUser }}</span>
                <i class="fa-solid fa-users text-teal-200 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/60 flex flex-col justify-between hover:shadow-md transition">
            <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Order Hari Ini</div>
            <div class="flex justify-between items-end">
                <span class="text-3xl font-black text-blue-600">{{ $totalTransaksiHariIni }}</span>
                <i class="fa-solid fa-receipt text-indigo-200 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/60 flex flex-col justify-between hover:shadow-md transition">
            <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Omzet Hari Ini</div>
            <div class="flex justify-between items-end">
                <span class="text-xl font-black text-teal-600">Rp{{ number_format($omzetHariIni, 0, ',', '.') }}</span>
                <i class="fa-solid fa-coins text-yellow-300 text-3xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2"><i class="fa-solid fa-chart-area text-blue-500"></i> Pendapatan</h3>
                    <div class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">7 Hari Terakhir</div>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-crown text-yellow-500"></i> Paling Laris
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-50 rounded-lg">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Produk</th>
                                <th class="px-4 py-3 text-center">Terjual</th>
                                <th class="px-4 py-3 text-right rounded-r-lg">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produkTerlaris as $index => $item)
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-bold text-gray-700 flex items-center gap-3">
                                    <div class="w-6 h-6 rounded bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold shadow-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="truncate max-w-[150px]">{{ $item->nama_produk }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-blue-50 text-blue-600 text-xs font-bold px-2 py-0.5 rounded">{{ $item->total_terjual }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $item->stok <= 15 ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ $item->stok }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-400">Belum ada data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 relative overflow-hidden">
                <div class="absolute -top-6 -right-6 w-24 h-24 bg-red-100 rounded-full opacity-50 blur-xl"></div>
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Stok Menipis
                </h3>
                <div class="space-y-3 relative z-10">
                    @forelse($stokHampirHabis as $item)
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100 hover:border-red-200 hover:bg-red-50 transition group">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse shrink-0"></div>
                            <div>
                                <p class="font-bold text-gray-700 text-sm truncate max-w-[100px]">{{ $item->nama_produk }}</p>
                                <p class="text-[10px] text-gray-400 group-hover:text-red-400">Sisa: <span class="font-bold">{{ $item->stok }}</span></p>
                            </div>
                        </div>
                        <a href="{{ route('inventaris.produk.edit', $item->id_produk) }}" class="text-[10px] font-bold bg-white text-gray-500 border border-gray-200 px-3 py-1.5 rounded-lg hover:text-red-500 hover:border-red-200 shadow-sm transition">
                            + Isi
                        </a>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-400 text-xs">Stok aman terkendali.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 relative overflow-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-500"></i> Transaksi Terbaru
                    </h3>
                    <a href="{{ route('transaksi.index') }}" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-3">
                    @forelse($transaksiTerbaru as $trx)
                    <div onclick="openDetailModal('{{ $trx->id_transaksi }}')" class="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50/50 border border-transparent hover:border-blue-100 transition cursor-pointer group">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 
                                {{ $trx->status == 'Selesai' ? 'bg-green-100 text-green-600' : ($trx->status == 'Dikirim' ? 'bg-blue-100 text-blue-600' : 'bg-yellow-100 text-yellow-600') }}">
                            <i class="fa-solid {{ $trx->status == 'Selesai' ? 'fa-check' : ($trx->status == 'Dikirim' ? 'fa-truck' : 'fa-box') }}"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-blue-600 transition">{{ $trx->kode_transaksi }}</p>
                                <p class="text-[10px] text-gray-400">{{ $trx->created_at->diffForHumans() }}</p>
                            </div>
                            <p class="text-[11px] text-gray-500 truncate">{{ $trx->user->nama ?? 'Guest' }} • <span class="font-bold text-gray-700">Rp{{ number_format($trx->total_bayar,0,',','.') }}</span></p>
                        </div>
                    </div>

                    <div id="data-trx-{{ $trx->id_transaksi }}" class="hidden">
                        <div class="json-kode">{{ $trx->kode_transaksi }}</div>
                        <div class="json-tgl">{{ date('d F Y, H:i', strtotime($trx->created_at)) }}</div>
                        <div class="json-status">{{ $trx->status }}</div>
                        <div class="json-pembeli">{{ $trx->user->nama ?? '-' }}</div>
                        <div class="json-hp">{{ $trx->user->no_hp ?? '-' }}</div>
                        <div class="json-alamat">{{ $trx->alamat->detail_alamat ?? 'Alamat tidak ditemukan' }}</div>
                        <div class="json-total">Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</div>
                        <div class="json-ongkir">Rp{{ number_format($trx->ongkos_kirim ?? 0, 0, ',', '.') }}</div>
                        <div class="json-produk">
                            @foreach($trx->detailTransaksi as $dt)
                            <div class="item-produk"
                                data-nama="{{ $dt->produk->nama_produk ?? 'Produk dihapus' }}"
                                data-qty="{{ $dt->jumlah }}"
                                data-harga="Rp{{ number_format($dt->harga_produk_saat_beli, 0, ',', '.') }}"
                                data-subtotal="Rp{{ number_format($dt->jumlah * $dt->harga_produk_saat_beli, 0, ',', '.') }}"
                                data-img="{{ $dt->produk->gambar ? asset('storage/'.$dt->produk->gambar) : '' }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-400 text-xs">Belum ada transaksi.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<div id="modalDetail" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modalPanel">

                <div class="bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold leading-6 text-white flex items-center gap-2">
                        <i class="fa-solid fa-receipt"></i> Detail Pesanan
                    </h3>
                    <button type="button" onclick="closeDetailModal()" class="text-white/70 hover:text-white transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-5 max-h-[70vh] overflow-y-auto custom-scrollbar">

                    <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-100">
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Kode Transaksi</p>
                            <p class="text-lg font-black text-gray-800" id="m-kode">TRX-0000</p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                <i class="fa-regular fa-calendar"></i> <span id="m-tgl">-</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span id="m-status" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">STATUS</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-red-500"></i> Info Pengiriman
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex">
                                <span class="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">Penerima</span>
                                <span class="font-bold text-gray-800" id="m-pembeli">-</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">Telepon</span>
                                <span class="text-gray-600" id="m-hp">-</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">Alamat</span>
                                <span class="text-gray-600 leading-relaxed" id="m-alamat">-</span>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-box text-teal-500"></i> Rincian Barang
                    </h4>
                    <div class="space-y-3 mb-6" id="m-produk-list"></div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                    <div class="text-xs text-gray-500 font-bold">
                        Ongkir: <span id="m-ongkir" class="text-gray-800">Rp0</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Total Bayar</p>
                        <p class="text-xl font-black text-blue-600" id="m-total">Rp0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- 1. SCRIPT CHART ---
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(20, 184, 166, 0.5)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Pendapatan',
                data: @json($chartData),
                borderColor: '#0d9488',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
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
                        callback: function(v) {
                            return 'Rp ' + (v / 1000) + 'k';
                        },
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

    // --- 2. SCRIPT MODAL DETAIL (Reuse) ---
    function openDetailModal(id) {
        const container = document.getElementById('data-trx-' + id);
        if (!container) return;

        document.getElementById('m-kode').innerText = container.querySelector('.json-kode').innerText;
        document.getElementById('m-tgl').innerText = container.querySelector('.json-tgl').innerText;
        document.getElementById('m-status').innerText = container.querySelector('.json-status').innerText;
        document.getElementById('m-pembeli').innerText = container.querySelector('.json-pembeli').innerText;
        document.getElementById('m-hp').innerText = container.querySelector('.json-hp').innerText;
        document.getElementById('m-alamat').innerText = container.querySelector('.json-alamat').innerText;
        document.getElementById('m-total').innerText = container.querySelector('.json-total').innerText;
        document.getElementById('m-ongkir').innerText = container.querySelector('.json-ongkir').innerText;

        const status = container.querySelector('.json-status').innerText;
        const statusEl = document.getElementById('m-status');
        statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold border ';
        if (status === 'Dikemas') statusEl.classList.add('bg-yellow-100', 'text-yellow-700', 'border-yellow-200');
        else if (status === 'Dikirim') statusEl.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
        else statusEl.classList.add('bg-green-100', 'text-green-700', 'border-green-200');

        const listContainer = document.getElementById('m-produk-list');
        listContainer.innerHTML = '';

        const items = container.querySelectorAll('.item-produk');
        items.forEach(item => {
            const imgUrl = item.dataset.img;
            const nama = item.dataset.nama;
            const qty = item.dataset.qty;
            const harga = item.dataset.harga;
            const subtotal = item.dataset.subtotal;

            const html = `
                    <div class="flex items-start gap-3 p-2 hover:bg-gray-50 rounded-lg transition border border-transparent hover:border-gray-100">
                        <div class="w-12 h-12 bg-white rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                            ${imgUrl ? `<img src="${imgUrl}" class="w-full h-full object-contain">` : '<i class="fa-solid fa-box text-gray-300"></i>'}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm line-clamp-2 leading-tight">${nama}</p>
                            <p class="text-xs text-gray-500 mt-1">${qty} x ${harga}</p>
                        </div>
                        <div class="font-bold text-gray-800 text-sm">${subtotal}</div>
                    </div>
                `;
            listContainer.insertAdjacentHTML('beforeend', html);
        });

        const modal = document.getElementById('modalDetail');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    }

    function closeDetailModal() {
        const modal = document.getElementById('modalDetail');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    document.getElementById('modalBackdrop').addEventListener('click', closeDetailModal);
</script>
@endsection