@extends('layouts.admin')

@section('title', 'Kelola Pesanan')
@section('header_title', 'Daftar Pesanan')

@section('content')

{{-- Custom Style (Scrollbar & Modal) --}}
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

    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight drop-shadow-sm">Daftar Pesanan</h1>
            <p class="text-gray-500 mt-1 text-sm font-medium">Pantau dan kelola status pesanan pelanggan secara realtime.</p>
        </div>

        <div class="flex gap-2">
            <span class="bg-white border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold text-gray-600 shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-filter text-blue-500"></i> Total: {{ $transaksi->count() }} Pesanan
            </span>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-xl border border-white/40 overflow-hidden min-h-[500px]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-xs font-extrabold tracking-wider">
                    <tr>
                        <th class="px-6 py-5">Info Transaksi</th>
                        <th class="px-6 py-5">Pelanggan</th>
                        <th class="px-6 py-5 bg-blue-50/50 text-blue-600 border-b border-blue-100">Dikirim Oleh</th>
                        <th class="px-6 py-5">Ringkasan Item</th>
                        <th class="px-6 py-5">Total Bayar</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-6 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($transaksi as $trx)
                    <tr class="hover:bg-blue-50/30 transition group">

                        <td class="px-6 py-4 cursor-pointer" onclick="openDetailModal('{{ $trx->id_transaksi }}')">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shadow-sm group-hover:scale-110 transition">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-800 block text-base hover:text-blue-600 transition underline-offset-2 decoration-blue-300 group-hover:underline">
                                        {{ $trx->kode_transaksi }}
                                    </span>
                                    <span class="text-gray-400 text-xs font-medium flex items-center gap-1">
                                        <i class="fa-regular fa-clock"></i> {{ date('d M Y, H:i', strtotime($trx->created_at)) }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700 text-sm">{{ $trx->user->nama ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500 font-medium">{{ $trx->user->no_hp ?? '-' }}</div>
                        </td>

                        <td class="px-6 py-4 bg-blue-50/30">
                            @if($trx->kurir)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-white border border-blue-200 text-blue-600 flex items-center justify-center text-xs font-bold shadow-sm">
                                    {{ substr($trx->kurir->nama, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-700 text-xs">{{ Str::limit($trx->kurir->nama, 15) }}</div>
                                    <div class="text-[9px] text-blue-600 font-bold uppercase tracking-wider bg-blue-100 px-1.5 py-0.5 rounded inline-block mt-0.5">Kurir</div>
                                </div>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic flex items-center gap-1 opacity-70">
                                <i class="fa-solid fa-user-slash"></i> Belum ada
                            </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 cursor-pointer" onclick="openDetailModal('{{ $trx->id_transaksi }}')">
                            <div class="flex flex-col gap-1 max-w-[200px]">
                                @foreach($trx->detailTransaksi->take(2) as $detail)
                                <div class="text-xs text-gray-600 truncate">
                                    <span class="font-bold text-gray-800 bg-gray-100 px-1.5 py-0.5 rounded mr-1 border border-gray-200">{{ $detail->jumlah }}x</span>
                                    {{ $detail->produk->nama_produk ?? 'Produk Dihapus' }}
                                </div>
                                @endforeach
                                @if($trx->detailTransaksi->count() > 2)
                                <span class="text-[10px] text-blue-500 font-bold hover:underline ml-1">+ {{ $trx->detailTransaksi->count() - 2 }} lainnya</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 font-extrabold text-gray-800">
                            Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                            $status = $trx->status;
                            $bg = 'bg-gray-100 text-gray-600 border-gray-200';
                            $icon = 'fa-circle-question';

                            if($status == 'Dikemas') { $bg = 'bg-yellow-50 text-yellow-700 border-yellow-200'; $icon='fa-box'; }
                            if($status == 'Dikirim') { $bg = 'bg-blue-50 text-blue-700 border-blue-200'; $icon='fa-truck-fast'; }
                            if($status == 'Selesai') { $bg = 'bg-green-50 text-green-700 border-green-200'; $icon='fa-check-circle'; }
                            @endphp
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $bg }} flex items-center justify-center gap-1.5 w-fit mx-auto shadow-sm">
                                <i class="fa-solid {{ $icon }}"></i> {{ strtoupper($status) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($trx->status == 'Dikemas')
                            <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Dikirim">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md shadow-blue-200 transition flex items-center gap-2 mx-auto transform active:scale-95">
                                    <i class="fa-solid fa-truck-fast"></i> Kirim
                                </button>
                            </form>

                            @elseif($trx->status == 'Dikirim')
                            <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md shadow-green-200 transition flex items-center gap-2 mx-auto transform active:scale-95">
                                    <i class="fa-solid fa-check-double"></i> Selesai
                                </button>
                            </form>

                            @else
                            <button onclick="openDetailModal('{{ $trx->id_transaksi }}')" class="text-gray-400 hover:text-blue-600 transition text-sm font-bold flex items-center gap-1 mx-auto">
                                <i class="fa-regular fa-eye"></i> Detail
                            </button>
                            @endif
                        </td>
                    </tr>

                    <div id="data-trx-{{ $trx->id_transaksi }}" class="hidden">
                        <div class="json-kode">{{ $trx->kode_transaksi }}</div>
                        <div class="json-tgl">{{ date('d F Y, H:i', strtotime($trx->created_at)) }}</div>
                        <div class="json-status">{{ $trx->status }}</div>
                        <div class="json-pembeli">{{ $trx->user->nama }}</div>
                        <div class="json-hp">{{ $trx->user->no_hp }}</div>
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
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-gray-200">
                                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                                </div>
                                <p class="font-bold text-gray-500 text-lg">Belum ada pesanan.</p>
                                <p class="text-xs mt-1 opacity-70">Pesanan baru akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($transaksi, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $transaksi->links() }}
        </div>
        @endif
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