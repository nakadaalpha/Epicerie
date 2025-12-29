<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Belanja - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap'); body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-700 pb-20 font-sans">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1000px] mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-[300px] shrink-0">
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm sticky top-24">
                
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden mb-5 flex items-center justify-center relative group border border-gray-100">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300 font-bold text-7xl">
                            {{ substr(Auth::user()->nama, 0, 1) }}
                        </div>
                    @endif
                </div>

                <div class="text-center mb-6">
                    <h3 class="font-bold text-gray-800 text-lg">{{ Auth::user()->nama }}</h3>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Menu Akun</p>
                    <nav class="space-y-1">
                        <a href="{{ route('kiosk.profile') }}" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-regular fa-user"></i></div> Biodata Diri
                        </a>

                        <a href="{{ route('kiosk.riwayat') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-600 font-bold rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-solid fa-clock-rotate-left"></i></div> Riwayat Transaksi
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-50 hover:text-red-600 font-bold rounded-xl transition text-left mt-2">
                                <div class="w-6 text-center"><i class="fa-solid fa-arrow-right-from-bracket"></i></div> Keluar
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
        </div>

        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-bag-shopping text-blue-600"></i> Pesanan Saya
            </h1>

            <div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-1 no-scrollbar">
                <a href="{{ route('kiosk.riwayat') }}" 
                   class="{{ !request('status') ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-4 py-2 rounded-t-lg transition whitespace-nowrap text-sm">
                   Semua
                </a>
                
                <a href="{{ route('kiosk.riwayat', ['status' => 'Dikemas']) }}" 
                   class="{{ request('status') == 'Dikemas' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-4 py-2 rounded-t-lg transition whitespace-nowrap text-sm">
                   <i class="fa-solid fa-box-open mr-1"></i> Dikemas
                </a>
                
                <a href="{{ route('kiosk.riwayat', ['status' => 'Dikirim']) }}" 
                   class="{{ request('status') == 'Dikirim' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-4 py-2 rounded-t-lg transition whitespace-nowrap text-sm">
                   <i class="fa-solid fa-truck-fast mr-1"></i> Dikirim
                </a>
                
                <a href="{{ route('kiosk.riwayat', ['status' => 'Selesai']) }}" 
                   class="{{ request('status') == 'Selesai' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-4 py-2 rounded-t-lg transition whitespace-nowrap text-sm">
                   <i class="fa-solid fa-check-circle mr-1"></i> Selesai
                </a>
            </div>

            @if($riwayat->isEmpty())
            <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-200">
                    <i class="fa-solid fa-receipt text-4xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Tidak ada pesanan {{ request('status') ? strtolower(request('status')) : '' }}</h3>
                <p class="text-gray-500 mb-6 text-sm">Yuk mulai belanja dan penuhi kebutuhanmu sekarang!</p>
                <a href="{{ route('kiosk.index') }}" class="inline-block bg-blue-600 text-white font-bold py-2.5 px-8 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                    Mulai Belanja
                </a>
            </div>
            @else

            <div class="space-y-6">
                @foreach($riwayat as $trx)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                    
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between md:items-center gap-3">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                            <div>
                                <span class="font-bold text-gray-800 block">{{ $trx->kode_transaksi }}</span>
                                <span class="text-xs text-gray-500">{{ date('d M Y, H:i', strtotime($trx->created_at)) }}</span>
                            </div>
                        </div>
                        
                        <div>
                            @php
                                $status = $trx->status ?? 'Dikemas';
                                $badgeColor = 'bg-yellow-100 text-yellow-700 border border-yellow-200'; 
                                $icon = 'fa-box';
                                if(strtolower($status) == 'dikirim') { $badgeColor = 'bg-blue-100 text-blue-700 border border-blue-200'; $icon='fa-truck'; }
                                if(strtolower($status) == 'selesai') { $badgeColor = 'bg-green-100 text-green-700 border border-green-200'; $icon='fa-check'; }
                                if(strtolower($status) == 'batal') { $badgeColor = 'bg-red-100 text-red-700 border border-red-200'; $icon='fa-xmark'; }
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badgeColor }}">
                                <i class="fa-solid {{ $icon }}"></i> {{ strtoupper($status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        @foreach($trx->detailTransaksi as $detail)
                        <div class="flex gap-4 mb-4 last:mb-0 items-start">
                            <div class="w-16 h-16 bg-white rounded-xl border border-gray-100 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                @if($detail->produk && $detail->produk->gambar)
                                <img src="{{ asset('storage/' . $detail->produk->gambar) }}" class="w-full h-full object-contain">
                                @else
                                <i class="fa-solid fa-box text-gray-300 text-xl"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $detail->produk->nama_produk ?? 'Produk Tidak Ditemukan' }}</h4>
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-gray-500 text-xs">{{ $detail->jumlah }} barang</p>
                                    <p class="text-gray-700 font-bold text-xs">Rp{{ number_format($detail->harga_produk_saat_beli, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center bg-white">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Total Belanja</span>
                            <p class="font-bold text-blue-600 text-lg">Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex gap-3">
                            @if(in_array($trx->status, ['Dikemas', 'Dikirim']))
                            <a href="{{ route('kiosk.tracking', $trx->id_transaksi) }}" class="px-5 py-2 bg-orange-50 text-orange-600 border border-orange-200 text-sm font-bold rounded-xl hover:bg-orange-100 transition">
                                <i class="fa-solid fa-map-location-dot mr-1"></i> Lacak
                            </a>
                            @endif

                            <a href="{{ route('kiosk.success', $trx->id_transaksi) }}" class="px-5 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 transition bg-white">
                                Detail
                            </a>
                            <a href="#" class="px-5 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                                Beli Lagi
                            </a>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</body>
</html>