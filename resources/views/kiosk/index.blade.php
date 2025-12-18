<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Épicerie Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hide-scroll::-webkit-scrollbar {
            display: none;
        }

        .hide-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">

    <div class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 pt-4 pb-2 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-blue-600">Épicerie</h1>
                <p class="text-xs text-gray-500">Kasir Tablet</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('kiosk.pending') }}" class="relative p-2 text-gray-600 hover:text-orange-500" title="Pesanan Tertunda">
                    <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                </a>

                <a href="{{ route('kiosk.checkout') }}" class="relative p-2 text-gray-600 hover:text-blue-600">
                    <i class="fa-solid fa-cart-shopping text-2xl"></i>
                    @if($totalItemKeranjang > 0)
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $totalItemKeranjang }}
                    </span>
                    @endif
                </a>
            </div>
        </div>

        <div class="px-4 pb-2 max-w-7xl mx-auto">
            <form action="{{ route('kiosk.index') }}" method="GET" class="relative">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full bg-gray-100 rounded-xl py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                <button type="submit" class="hidden"></button>
            </form>
        </div>

        <div class="pl-4 pb-3 max-w-7xl mx-auto overflow-x-auto hide-scroll flex gap-2 whitespace-nowrap">
            <a href="{{ route('kiosk.index') }}" class="px-4 py-1.5 rounded-full text-sm font-bold border transition {{ !request('kategori') || request('kategori') == 'semua' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400' }}">
                Semua
            </a>
            @foreach($kategoriList as $kat)
            <a href="{{ route('kiosk.index', ['kategori' => $kat->id_kategori]) }}" class="px-4 py-1.5 rounded-full text-sm font-bold border transition {{ request('kategori') == $kat->id_kategori ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400' }}">
                {{ $kat->nama_kategori }}
            </a>
            @endforeach
        </div>
    </div>

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    </div>
    @endif
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(isset($daftarPaket) && count($daftarPaket) > 0)
    <div class="max-w-7xl mx-auto px-4 mt-6">
        <div class="flex items-center gap-2 mb-3">
            <h2 class="font-bold text-gray-800 text-lg">⚡ Paket Kilat</h2>
            <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">Hemat Waktu!</span>
        </div>

        <div class="flex gap-4 overflow-x-auto hide-scroll pb-2">
            @foreach($daftarPaket as $key => $paket)
            <a href="{{ route('kiosk.add.packet', $key) }}" class="flex-shrink-0 w-64 {{ $paket['warna'] }} rounded-2xl p-4 text-white shadow-lg transform hover:-translate-y-1 transition relative overflow-hidden group">
                <div class="absolute right-[-10px] bottom-[-10px] text-8xl opacity-20 group-hover:scale-125 transition duration-500">
                    {{ $paket['ikon'] }}
                </div>

                <div class="relative z-10">
                    <div class="text-3xl mb-1">{{ $paket['ikon'] }}</div>
                    <h3 class="font-bold text-xl leading-tight mb-1">{{ $paket['nama'] }}</h3>
                    <p class="text-white/90 text-xs mb-3 font-medium">{{ $paket['harga_display'] }}</p>

                    <div class="bg-white/20 rounded-lg p-2 backdrop-blur-sm">
                        <ul class="text-xs space-y-1">
                            @foreach($paket['items'] as $item)
                            <li class="flex items-center gap-1">
                                <i class="fa-solid fa-check text-[10px]"></i> {{ $item['qty'] }}x {{ $item['keyword'] }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-3 bg-white text-gray-800 text-center py-2 rounded-lg font-bold text-sm shadow hover:bg-gray-100 transition">
                        Ambil Paket
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="max-w-7xl mx-auto px-4 mt-6 text-red-500 hidden">
        (Debug: Variabel Daftar Paket Kosong / Tidak Terkirim dari Controller)
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 pb-24 mt-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-bold text-gray-700">Daftar Produk</h2>
            @if(request('search') || request('kategori'))
            <a href="{{ route('kiosk.index') }}" class="text-xs text-blue-600 underline">Reset Filter</a>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @forelse($produk as $p)
            @php
            $qty = $keranjangItems[$p->id_produk] ?? 0;
            @endphp

<<<<<<< HEAD
            <div class="bg-white p-3 rounded-2xl shadow-sm border {{ $qty > 0 ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-100' }} flex flex-col justify-between transition-all hover:shadow-md">
                <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative group">
                    <div class="text-4xl m-5 group-hover:scale-110 transition duration-300">
                        <img src="{{ asset('storage/' . $p->gambar) }}" alt=" {{ $p->nama_produk }}">
                    </div>
                    @if($qty > 0)
                    <div class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow z-10">
                        {{ $qty }}x
                    </div>
                    @endif

                    <button onclick="openQtyModal({{ $p->id_produk }}, '{{ $p->nama_produk }}', {{ $qty }})" class="absolute top-2 left-2 bg-white/90 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-orange-500 hover:text-white transition z-20" title="Input Jumlah Manual">
                        <i class="fa-solid fa-calculator text-xs"></i>
                    </button>
                </div>

                <div>
                    <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>
                    <p class="text-xs text-gray-500 mb-2 truncate">{{ $p->deskripsi_produk }}</p>

                    <div class="flex justify-between items-end">
                        <span class="text-blue-600 font-bold text-sm mb-1">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>

                        @if($p->stok > 0)
                        @if($qty > 0)
                        <div class="flex items-center bg-gray-100 rounded-full p-1 gap-2 shadow-inner">
                            <a href="{{ route('kiosk.decrease', $p->id_produk) }}" class="w-6 h-6 bg-white text-gray-600 rounded-full flex items-center justify-center shadow-sm active:scale-90 transition hover:bg-red-100 hover:text-red-600">
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </a>
                            <span class="text-xs font-bold text-gray-700 w-3 text-center">{{ $qty }}</span>
                            <a href="{{ route('kiosk.add', $p->id_produk) }}" class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-sm active:scale-90 transition hover:bg-blue-700">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </a>
                        </div>
                        @else
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @endif
                        @else
                        <span class="text-xs text-red-500 font-bold mb-1">Habis</span>
                        @endif
=======
            <div class="{{ $qty > 0 ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-100' }} flex flex-col justify-between transition-all hover:shadow-md">

            <!-- Bungkus bagian visual dan teks dengan link -->
            <a href="{{ route('produk.show', $p->id_produk) }}" class="bg-white p-3 rounded-2xl shadow-sm block hover:shadow-lg transition">
                <div class="aspect-square bg-blue-50 rounded-xl mb-3 flex items-center justify-center overflow-hidden relative group">
                <span class="text-4xl group-hover:scale-110 transition duration-300">📦</span>
                @if($qty > 0)
                    <div class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow z-10">
                    {{ $qty }}x
>>>>>>> 2def2f0b1d872010fadd827cd237023ba6ebb6e1
                    </div>
                @endif
                <button onclick="openQtyModal({{ $p->id_produk }}, '{{ $p->nama_produk }}', {{ $qty }})"
                        class="absolute top-2 left-2 bg-white/90 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-orange-500 hover:text-white transition z-20"
                        title="Input Jumlah Manual">
                    <i class="fa-solid fa-calculator text-xs"></i>
                </button>
                </div>

                <div>
                <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>
                <p class="text-xs text-gray-500 mb-2 truncate">{{ $p->deskripsi_produk }}</p>
                <span class="text-blue-600 font-bold text-sm mb-1 block">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                </div>
            </a>

            <!-- Tombol plus/minus tetap di luar link -->
            <div class="mt-2">
                @if($p->stok > 0)
                @if($qty > 0)
                    <div class="flex items-center bg-gray-100 rounded-full p-1 gap-2 shadow-inner">
                    <a href="{{ route('kiosk.decrease', $p->id_produk) }}" class="w-6 h-6 bg-white text-gray-600 rounded-full flex items-center justify-center shadow-sm active:scale-90 transition hover:bg-red-100 hover:text-red-600">
                        <i class="fa-solid fa-minus text-[10px]"></i>
                    </a>
                    <span class="text-xs font-bold text-gray-700 w-3 text-center">{{ $qty }}</span>
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-sm active:scale-90 transition hover:bg-blue-700">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                    </a>
                    </div>
                @else
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i>
                    </a>
                @endif
                @else
                <span class="text-xs text-red-500 font-bold mb-1">Habis</span>
                @endif
            </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Produk tidak ditemukan.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if($totalItemKeranjang > 0)
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-40">
        <div class="max-w-7xl mx-auto flex gap-4">
            <form action="{{ route('kiosk.hold') }}" method="POST" class="w-1/3 md:w-1/4">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="nama_hold" placeholder="Nama..." required class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-orange-500">
                    <button type="submit" class="bg-orange-500 text-white px-4 py-3 rounded-lg font-bold shadow hover:bg-orange-600 transition whitespace-nowrap">
                        <i class="fa-solid fa-pause"></i>
                    </button>
                </div>
            </form>

            <a href="{{ route('kiosk.checkout') }}" class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg font-bold shadow hover:bg-blue-700 transition flex items-center justify-center gap-2 text-lg">
                Bayar Sekarang <span class="bg-white/20 px-2 py-0.5 rounded text-sm ml-2">{{ $totalItemKeranjang }} Item</span>
            </a>
        </div>
    </div>
    @endif

    @if(session('rekomendasi_produk') && count(session('rekomendasi_produk')) > 0)
    <div id="modalRekomendasi" class="fixed inset-0 z-[60] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('modalRekomendasi').remove()"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden animate-[bounce_0.5s_ease-out]">
            <div class="bg-blue-600 p-4 text-white text-center">
                <p class="text-sm opacity-90">Produk berhasil ditambahkan! ✅</p>
                <h3 class="font-bold text-lg mt-1">Lengkapi Belanjaan Kamu?</h3>
            </div>
            <div class="p-4 bg-blue-50">
                <div class="space-y-3">
                    @foreach(session('rekomendasi_produk') as $rek)
                    <div class="bg-white p-3 rounded-xl flex justify-between items-center shadow-sm border border-blue-100">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✨</span>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $rek->nama_produk }}</p>
                                <p class="text-xs text-blue-600 font-bold">Rp {{ number_format($rek->harga_produk, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('kiosk.add', $rek->id_produk) }}" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-3 py-2 rounded-lg shadow transition">
                            + Ambil
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="p-3 bg-white border-t text-center">
                <button onclick="document.getElementById('modalRekomendasi').remove()" class="text-gray-500 text-sm hover:text-gray-800 font-medium w-full py-2">
                    Nggak dulu, makasih
                </button>
            </div>
        </div>
    </div>
    @endif

    <div id="modalQty" class="fixed inset-0 z-[70] hidden flex items-end sm:items-center justify-center px-4 pb-4 sm:pb-0">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeQtyModal()"></div>

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden transform transition-all scale-100">
            <div class="p-5">
                <h3 class="text-lg font-bold text-gray-800 mb-1" id="modalQtyTitle">Input Jumlah</h3>
                <p class="text-sm text-gray-500 mb-4">Masukkan jumlah barang yang ingin dibeli.</p>

                <form id="formQty" action="" method="POST">
                    @csrf
                    <div class="flex gap-2 mb-4">
                        <input type="number" name="qty" id="inputQty" class="w-full bg-gray-100 border-2 border-gray-200 rounded-xl px-4 py-3 text-2xl font-bold text-center focus:outline-none focus:border-blue-500 focus:bg-white transition" placeholder="0" min="1" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="closeQtyModal()" class="py-3 rounded-xl font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            var scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        });

        window.onbeforeunload = function(e) {
            sessionStorage.setItem('scrollpos', window.scrollY);
        };

        function openQtyModal(id, name, currentQty) {
            const modal = document.getElementById('modalQty');
            const form = document.getElementById('formQty');
            const title = document.getElementById('modalQtyTitle');
            const input = document.getElementById('inputQty');

            form.action = "/kiosk/set-qty/" + id;
            title.innerText = name;
            input.value = currentQty > 0 ? currentQty : '';

            modal.classList.remove('hidden');
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        }

        function closeQtyModal() {
            document.getElementById('modalQty').classList.add('hidden');
        }
    </script>

</body>

</html>