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

<body class="font-sans">

    @include('partials.navbar-kiosk')

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

            <div class="bg-white p-3 rounded-2xl shadow-sm border flex flex-col justify-between transition-all hover:shadow-md relative group">

                <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                    <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                        @else
                        <span class="text-4xl">📦</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>
                    <p class="text-xs text-gray-500 mb-2 truncate">{{ $p->deskripsi_produk }}</p>
                    <span class="text-blue-600 font-bold text-sm mb-1 block">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</span>
                </a>

                <div class="flex justify-end mt-2 z-20 relative">
                    @if($p->stok > 0)
                    <a href="{{ route('kiosk.add', $p->id_produk) }}"
                        class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    @else
                    <span class="text-xs text-red-500 font-bold mb-1 py-1">Habis</span>
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