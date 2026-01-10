@extends('layouts.admin')

@section('title', 'Kelola Pesanan')
@section('header_title', 'Daftar Pesanan')

@section('content')

{{-- Style Khusus (Diambil dari Halaman Inventaris + Scrollbar Modal) --}}
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

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

    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
</style>

{{-- Wrapper Utama --}}
<div class="max-w-7xl mx-auto pb-20">

    {{-- SECTION FILTER (Style Glassmorphism) --}}
    <div class="mb-8">
        <form action="{{ route('transaksi.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">

            {{-- Search --}}
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode Transaksi / Nama Pembeli..."
                    class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40">
                <div class="absolute left-4 top-3.5 text-blue-500">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('transaksi.index') }}" class="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition" title="Reset Pencarian">
                    <i class="fa-solid fa-times text-lg"></i>
                </a>
                @endif
            </div>

            {{-- Filter Status --}}
            <div class="relative min-w-[180px]">
                <select name="status" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="">Semua Status</option>
                    <option value="Dikemas" {{ request('status') == 'Dikemas' ? 'selected' : '' }}>📦 Dikemas</option>
                    <option value="Dikirim" {{ request('status') == 'Dikirim' ? 'selected' : '' }}>🚚 Dikirim</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>✅ Selesai</option>
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            {{-- Sort --}}
            <div class="relative min-w-[180px]">
                <select name="sort" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="terbesar" {{ request('sort') == 'terbesar' ? 'selected' : '' }}>Nominal Tertinggi</option>
                    <option value="terkecil" {{ request('sort') == 'terkecil' ? 'selected' : '' }}>Nominal Terendah</option>
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none">
                    <i class="fa-solid fa-arrow-down-up-across-line"></i>
                </div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

        </form>
    </div>

    {{-- MAIN CONTENT CARD --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        {{-- Header Card --}}
        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Daftar Pesanan</h2>
                <p class="text-gray-400 text-sm mt-1">Kelola status dan pengiriman pesanan toko Anda.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                Total: {{ $transaksi->total() }} Item
            </span>
        </div>

        {{-- Table Header (Desktop View) --}}
        <div class="hidden md:flex items-center px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-20">Transaksi</div>
            <div class="w-48">Pelanggan & Kurir</div>
            <div class="flex-1">Item Preview</div>
            <div class="w-32">Total</div>
            <div class="w-32 text-center">Status</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        {{-- LIST DATA --}}
        <div class="flex flex-col gap-3">

            @forelse($transaksi as $trx)
            {{-- Logic Warna Garis Tepi berdasarkan Status --}}
            @php
            $statusLower = strtolower($trx->status);
            $borderColor = 'bg-gray-500';
            if(str_contains($statusLower, 'selesai')) $borderColor = 'bg-green-500';
            elseif(str_contains($statusLower, 'kirim')) $borderColor = 'bg-blue-500';
            elseif(str_contains($statusLower, 'kemas')) $borderColor = 'bg-yellow-500';
            @endphp

            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                {{-- Garis Indikator Status --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $borderColor }} opacity-0 group-hover:opacity-100 transition-opacity"></div>

                {{-- Kolom 1: Icon & Kode --}}
                <div class="flex items-center w-full md:w-20 mb-3 md:mb-0 cursor-pointer" onclick="openDetailModal('{{ $trx->id_transaksi }}')">
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-bold shadow-sm border border-blue-100 group-hover:bg-white transition">
                            <i class="fa-solid fa-receipt text-xl"></i>
                        </div>
                    </div>
                    <div class="md:hidden ml-3">
                        <h3 class="font-bold text-gray-800 text-sm">{{ $trx->kode_transaksi }}</h3>
                        <p class="text-xs text-gray-400">{{ date('d M Y', strtotime($trx->created_at)) }}</p>
                    </div>
                </div>

                {{-- Kolom 2: Pelanggan & Kurir --}}
                <div class="w-full md:w-48 mb-2 md:mb-0 pr-2">
                    <h3 class="hidden md:block font-bold text-gray-800 text-sm truncate">{{ $trx->kode_transaksi }}</h3>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-600 truncate">{{ $trx->user->nama ?? 'Guest' }}</span>
                        @if($trx->kurir)
                        <div class="flex items-center gap-1 mt-1">
                            <i class="fa-solid fa-truck-fast text-[10px] text-gray-400"></i>
                            <span class="text-[10px] text-gray-500">{{ Str::limit($trx->kurir->nama, 15) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Kolom 3: Item Preview --}}
                <div class="flex-1 min-w-0 pr-4 mb-2 md:mb-0 cursor-pointer" onclick="openDetailModal('{{ $trx->id_transaksi }}')">
                    <div class="flex flex-col gap-1">
                        @foreach($trx->detailTransaksi->take(1) as $detail)
                        <span class="text-xs font-medium text-gray-600 truncate">
                            <span class="font-bold text-gray-800">{{ $detail->jumlah }}x</span> {{ $detail->produk->nama_produk ?? 'Produk Dihapus' }}
                        </span>
                        @endforeach
                        @if($trx->detailTransaksi->count() > 1)
                        <span class="text-[10px] font-bold text-blue-500">+{{ $trx->detailTransaksi->count() - 1 }} lainnya</span>
                        @endif
                    </div>
                </div>

                {{-- Kolom 4: Total --}}
                <div class="w-full md:w-32 flex items-center mb-2 md:mb-0">
                    <span class="font-extrabold text-blue-600 text-sm">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</span>
                </div>

                {{-- Kolom 5: Status Badge --}}
                <div class="w-full md:w-32 flex md:justify-center items-center mb-2 md:mb-0">
                    @php
                    $badgeClass = ''; $icon = '';
                    if(str_contains($statusLower, 'selesai')) {
                    $badgeClass = 'bg-green-50 text-green-600 border-green-100'; $icon = 'fa-circle-check';
                    } elseif(str_contains($statusLower, 'kirim')) {
                    $badgeClass = 'bg-blue-50 text-blue-600 border-blue-100'; $icon = 'fa-truck-fast';
                    } elseif(str_contains($statusLower, 'kemas') || str_contains($statusLower, 'proses')) {
                    $badgeClass = 'bg-yellow-50 text-yellow-600 border-yellow-100'; $icon = 'fa-box';
                    } else {
                    $badgeClass = 'bg-gray-50 text-gray-600 border-gray-100'; $icon = 'fa-circle-question';
                    }
                    @endphp
                    <span class="text-[10px] font-bold px-3 py-1 rounded-full border {{ $badgeClass }} flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid {{ $icon }}"></i> {{ strtoupper($trx->status) }}
                    </span>
                </div>

                {{-- Kolom 6: Aksi --}}
                <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">

                    {{-- Tombol Logic berdasarkan Status --}}
                    @if($trx->status == 'Dikemas')
                    <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="Dikirim">
                        <button type="submit"
                            class="bg-white text-blue-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition"
                            title="Kirim Pesanan">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </button>
                    </form>
                    @elseif($trx->status == 'Dikirim')
                    <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="Selesai">
                        <button type="submit"
                            class="bg-white text-green-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-green-500 hover:text-white hover:border-green-500 transition"
                            title="Selesaikan Pesanan">
                            <i class="fa-solid fa-check text-xs"></i>
                        </button>
                    </form>
                    @else
                    {{-- Tombol Lihat Detail (Jika status sudah selesai/lainnya) --}}
                    <button onclick="openDetailModal('{{ $trx->id_transaksi }}')"
                        class="bg-white text-gray-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-gray-700 hover:text-white hover:border-gray-700 transition"
                        title="Lihat Detail">
                        <i class="fa-regular fa-eye text-xs"></i>
                    </button>
                    @endif

                </div>

            </div>

            {{-- DATA HIDDEN UNTUK MODAL JS --}}
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
            <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                </div>
                @if(request('search') || request('status'))
                <h3 class="font-bold text-gray-600 text-lg">Pesanan tidak ditemukan.</h3>
                <p class="text-sm text-gray-400 mt-1">Coba kata kunci lain atau reset filter.</p>
                <a href="{{ route('transaksi.index') }}" class="mt-4 text-sm font-bold text-blue-500 hover:underline">Reset Filter</a>
                @else
                <h3 class="font-bold text-gray-600 text-lg">Belum ada pesanan.</h3>
                <p class="text-sm text-gray-400 mt-1">Pesanan masuk akan muncul di sini.</p>
                @endif
            </div>
            @endforelse

        </div>

        {{-- PAGINATION --}}
        @if($transaksi->hasPages())
        <div class="mt-8 border-t border-gray-100 pt-6">
            {{ $transaksi->links() }}
        </div>
        @endif

    </div>

</div>

{{-- MODAL DETAIL (Tetap menggunakan HTML yang lama, disesuaikan sedikit agar rapi) --}}
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

{{-- SCRIPT JS (Tidak Berubah) --}}
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

        const status = container.querySelector('.json-status').innerText.toLowerCase();
        const statusEl = document.getElementById('m-status');

        statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold border ';
        if (status.includes('kemas') || status.includes('proses')) {
            statusEl.classList.add('bg-yellow-100', 'text-yellow-700', 'border-yellow-200');
        } else if (status.includes('kirim')) {
            statusEl.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
        } else if (status.includes('selesai')) {
            statusEl.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
        } else {
            statusEl.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        }

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