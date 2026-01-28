<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $produk->nama_produk }} - Detail</title>
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

        .sticky-card {
            position: sticky;
            top: 100px;
        }

        @keyframes slideInDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .toast-enter {
            animation: slideInDown 0.4s ease-out forwards;
        }

        /* Star Hover Effect */
        .star-rating:hover .star-icon {
            color: #fbbf24;
        }
    </style>
</head>

<body class="bg-white font-sans text-gray-800 min-h-screen flex flex-col">

    {{-- TOAST --}}
    <div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[999] hidden flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300">
        <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"><i id="toast-icon" class="fa-solid text-lg"></i></div>
        <div class="ml-3 text-sm font-bold text-gray-800" id="toast-message">Pesan</div>
    </div>

    @include('partials.navbar-kiosk')

    <main class="flex-grow max-w-[1200px] mx-auto w-full px-4 py-6">

        {{-- BREADCRUMB --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mt-5 mb-4 overflow-x-auto whitespace-nowrap pb-2 border-b border-transparent">
            <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold hover:text-blue-800 transition">Beranda</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @if($produk->kategori)
            <a href="{{ route('kiosk.search', ['kategori[]' => $produk->id_kategori]) }}" class="text-blue-600 font-bold hover:text-blue-800 transition">{{ $produk->kategori->nama_kategori }}</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @endif
            <span class="text-gray-600 truncate font-medium max-w-[200px] md:max-w-md">{{ $produk->nama_produk }}</span>
        </nav>

        {{-- SECTION 1: INFO PRODUK UTAMA --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 relative mb-12">

            {{-- Foto Produk --}}
            <div class="lg:col-span-4">
                <div class="sticky top-24">
                    <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200 mb-4 cursor-zoom-in relative group shadow-sm">
                        @if($produk->persen_diskon > 0)
                        <div class="absolute top-3 left-3 z-10 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md flex items-center gap-1"><i class="fa-solid fa-tags"></i> Hemat {{ $produk->persen_diskon }}%</div>
                        @endif
                        @if($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-contain p-4 transition duration-500 group-hover:scale-105">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-blue-100"><i class="fa-solid fa-image text-6xl"></i></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detail Info --}}
            <div class="lg:col-span-5 flex flex-col gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-snug mb-2">{{ $produk->nama_produk }}</h1>
                    <div class="flex items-center gap-3 text-sm mb-4">
                        <div class="flex items-center gap-1 text-yellow-400">
                            <i class="fa-solid fa-star"></i>
                            <span class="text-gray-900 font-bold">{{ number_format($avgRating ?? 0, 1) }}</span>
                            <span class="text-gray-400 text-xs">({{ $totalUlasan ?? 0 }} Ulasan)</span>
                        </div>
                        <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                        <span class="text-gray-900 font-bold">Terjual {{ number_format($totalTerjual, 0, ',', '.') }}</span>
                    </div>

                    @if($produk->persen_diskon > 0)
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm text-gray-400 line-through decoration-red-400 decoration-2">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</span>
                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Diskon {{ $produk->persen_diskon }}%</span>
                    </div>
                    <h2 class="text-4xl font-extrabold text-blue-600">Rp{{ number_format($produk->harga_final, 0, ',', '.') }}</h2>
                    @else
                    <h2 class="text-4xl font-extrabold text-blue-600">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</h2>
                    @endif
                </div>

                <hr class="border-gray-100 my-2">

                <div class="space-y-2">
                    <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed text-justify">
                        <h3 class="font-bold text-gray-900 text-lg">Deskripsi Produk</h3>
                        <p>{{ $produk->deskripsi_produk ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>
            </div>

            {{-- Checkout Card (Sticky) --}}
            <div class="lg:col-span-3">
                <div class="sticky-card bg-white border border-gray-200 rounded-xl p-5  z-20">
                    <h3 class="font-bold text-gray-900 mb-4">Atur pesanan</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center border border-gray-300 rounded-lg p-1">
                            <button onclick="updateQty(-1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 font-bold transition"><i class="fa-solid fa-minus text-xs"></i></button>
                            <input type="text" id="qtyInput" value="1" class="w-10 text-center font-bold text-gray-700 outline-none text-sm bg-transparent" readonly>
                            <button onclick="updateQty(1)" class="w-7 h-7 flex items-center justify-center text-blue-600 hover:text-blue-700 font-bold transition"><i class="fa-solid fa-plus text-xs"></i></button>
                        </div>
                        <span class="text-sm text-gray-500">Stok: <span class="font-bold text-gray-800">{{ $produk->stok }}</span></span>
                    </div>
                    <div class="flex justify-between items-center mb-5">
                        <span class="text-gray-500 text-sm">Subtotal</span>
                        <div class="flex flex-col items-end">
                            @if($produk->harga_produk > $produk->harga_final)
                            <span class="text-xs text-gray-400 line-through decoration-gray-400" id="subtotalCoret">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</span>
                            @endif
                            <span class="font-extrabold text-xl text-gray-900" id="subtotalDisplay">Rp{{ number_format($produk->harga_final, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($produk->stok > 0)
                    <div class="flex flex-col gap-3">
                        <button id="btn-keranjang" onclick="submitCart('cart')" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-center transition"><i class="fa-solid fa-plus mr-2"></i> Keranjang</button>
                        <button onclick="submitCart('now')" class="block w-full border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-3 rounded-lg transition">Beli Langsung</button>
                    </div>
                    @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed">Stok Habis</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECTION 2: ULASAN PEMBELI (TERPISAH) --}}
        <div class="border-t border-gray-200 pt-8 mt-8 grid grid-cols-1 lg:grid-cols-12 gap-10" id="ulasan-section">
            <div class="lg:col-span-9">
                <h3 class="font-bold text-lg text-gray-900 mb-6 uppercase tracking-wider">Ulasan Pembeli</h3>

                @php
                // Hitung persentase kepuasan (Bintang 4 & 5)
                $total = $totalUlasan > 0 ? $totalUlasan : 1;
                $percentageSatisfied = (($starCounts[5] + $starCounts[4]) / $total) * 100;

                // Ambil rating yang sedang aktif dari URL
                $currentFilter = request('rating');
                @endphp

                <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8 flex flex-col md:flex-row gap-8 items-center shadow-sm">
                    {{-- Dashboard Kiri (Angka Besar) --}}
                    <div class="flex flex-col items-center justify-center min-w-[150px] border-r border-gray-100 pr-8">
                        <div class="flex items-baseline gap-1">
                            <i class="fa-solid fa-star text-yellow-400 text-4xl"></i>
                            <span class="text-6xl font-extrabold text-gray-900">{{ number_format($avgRating ?? 0, 1) }}</span>
                            <span class="text-gray-400 text-lg font-medium">/5.0</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 mt-2">{{ number_format($percentageSatisfied, 0) }}% pembeli merasa puas</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $totalUlasan }} rating • {{ $totalUlasan }} ulasan</p>
                    </div>

                    {{-- Dashboard Kanan (Filter Grafik) --}}
                    <div class="flex-1 w-full space-y-2">
                        @for($i = 5; $i >= 1; $i--)
                        @php
                        $percent = ($starCounts[$i] / $total) * 100;
                        $isActive = $currentFilter == $i;
                        @endphp

                        {{-- Ubah DIV menjadi A (Link) untuk Filter --}}
                        <a href="{{ route('produk.show', ['id' => $produk->id_produk, 'rating' => $i]) }}#ulasan-section"
                            class="flex items-center gap-3 text-xs group cursor-pointer transition-all rounded-lg px-2 py-1 {{ $isActive ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-gray-50' }}">

                            <div class="flex items-center gap-1 w-8 shrink-0 font-bold {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}">
                                <i class="fa-solid fa-star {{ $isActive ? 'text-blue-500' : 'text-yellow-400' }}"></i> {{ $i }}
                            </div>

                            <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $percent > 0 ? ($isActive ? 'bg-blue-600' : 'bg-blue-500') : 'bg-transparent' }}" style="width: {{ $percent }}%"></div>
                            </div>

                            <div class="w-8 text-right font-bold {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}">{{ $starCounts[$i] }}</div>
                        </a>
                        @endfor
                    </div>
                </div>

                {{-- Filter & List --}}
                <div class="space-y-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-gray-700">Filter:</span>
                            @if($currentFilter)
                            <a href="{{ route('produk.show', $produk->id_produk) }}#ulasan-section" class="px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-full border border-red-100 hover:bg-red-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-xmark"></i> Hapus Filter (Bintang {{ $currentFilter }})
                            </a>
                            @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs font-bold rounded-full border border-gray-200">Semua Bintang</span>
                            @endif
                        </div>
                    </div>

                    @if(isset($ulasan) && $ulasan->count() > 0)
                    @foreach($ulasan as $u)
                    <div class="border-b border-gray-100 pb-6 last:border-0">
                        <div class="flex items-center gap-2 mb-2 text-xs">
                            <div class="flex text-yellow-400">
                                @for($i=1; $i<=5; $i++) <i class="fa-{{ $i <= $u->rating ? 'solid' : 'regular' }} fa-star"></i> @endfor
                            </div>
                            <span class="text-gray-400">• {{ $u->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden shrink-0">
                                @if($u->user->foto_profil) <img src="{{ asset('storage/' . $u->user->foto_profil) }}" class="w-full h-full object-cover">
                                @else <div class="w-full h-full flex items-center justify-center text-gray-500 font-bold text-xs">{{ substr($u->user->nama ?? 'U', 0, 1) }}</div> @endif
                            </div>
                            <span class="font-bold text-sm text-gray-800">{{ $u->user->nama }}</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $u->komentar }}</p>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <i class="fa-solid fa-filter-circle-xmark text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm font-bold">Tidak ada ulasan dengan bintang {{ $currentFilter }}</p>
                        <a href="{{ route('produk.show', $produk->id_produk) }}#ulasan-section" class="text-blue-600 text-xs font-bold mt-2 inline-block hover:underline">Lihat Semua Ulasan</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECTION 3: PRODUK LAINNYA --}}
        @if(isset($produkLain) && count($produkLain) > 0)
        <div class="mt-16 border-t border-gray-200 pt-10">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-extrabold text-gray-900">Produk Lainnya</h3>
                <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold text-sm hover:underline">Lihat Semua</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($produkLain as $rek)
                @php
                $hasDiskonRek = $rek->persen_diskon > 0;
                $hargaFinalRek = $hasDiskonRek ? $rek->harga_produk - ($rek->harga_produk * ($rek->persen_diskon / 100)) : $rek->harga_produk;
                $ratingRek = $rek->ulasan->avg('rating') ?? 0;
                $terjualRek = $rek->total_terjual ?? 0;
                @endphp
                <div class="bg-white p-2 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between h-full relative overflow-hidden">
                    <div class="relative mb-2">
                        @if($hasDiskonRek) <div class="absolute top-0 left-0 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-sm">-{{ $rek->persen_diskon }}%</div> @endif
                        <a href="{{ route('produk.show', $rek->id_produk) }}" class="block aspect-square rounded-lg overflow-hidden">
                            @if($rek->gambar) <img src="{{ asset('storage/' . $rek->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-105 transition duration-300">
                            @else <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300"><i class="fa-solid fa-image"></i></div> @endif
                        </a>
                    </div>
                    <div class="flex flex-col flex-1">
                        <a href="{{ route('produk.show', $rek->id_produk) }}" class="block mb-1">
                            <h3 class="font-bold text-gray-800 text-xs leading-snug line-clamp-2 h-8" title="{{ $rek->nama_produk }}">{{ $rek->nama_produk }}</h3>
                        </a>
                        <div class="flex items-center gap-1 mb-1">
                            <i class="fa-solid fa-star text-[10px] text-yellow-400"></i>
                            <span class="text-[10px] text-gray-500">{{ number_format($ratingRek, 1) }} <span class="text-gray-300">•</span> {{ $terjualRek }} terjual</span>
                        </div>
                        <div class="mt-auto">
                            <div class="flex flex-wrap items-baseline gap-x-1.5">
                                <span class="text-sm font-extrabold text-blue-600">Rp{{ number_format($hargaFinalRek, 0, ',', '.') }}</span>
                                @if($hasDiskonRek) <span class="text-[10px] text-gray-400 line-through">Rp{{ number_format($rek->harga_produk, 0, ',', '.') }}</span> @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        @if($rek->stok > 0)
                        <a href="{{ route('kiosk.add', $rek->id_produk) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 rounded-lg text-center transition shadow-sm active:scale-95">+ Keranjang</a>
                        @else
                        <button disabled class="block w-full bg-gray-100 text-gray-400 font-bold text-xs py-1.5 rounded-lg text-center cursor-not-allowed">Habis</button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </main>

    @include('partials.footer-kiosk')

    <script>
        // === 1. PERBAIKAN SINTAKS PHP DI DALAM SCRIPT ===
        const maxStok = {{ $produk->stok ?? 0 }};
        const hargaFinalSatuan = {{ $produk->harga_final ?? $produk->harga_produk ?? 0 }};
        
        // Element Reference
        const qtyInput = document.getElementById('qtyInput');
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const cartBadge = document.getElementById('cart-badge');

        function formatRupiah(angka) {
            return 'Rp' + new Intl.NumberFormat('id-ID').format(angka);
        }

        // === 2. FUNGSI UPDATE QTY ===
        function updateQty(change) {
            let currentQty = parseInt(qtyInput.value) || 1;
            let newQty = currentQty + change;

            if (newQty >= 1 && newQty <= maxStok) {
                qtyInput.value = newQty;
                // Update Subtotal Realtime
                let total = newQty * hargaFinalSatuan;
                subtotalDisplay.innerText = formatRupiah(total);
            } else if (newQty > maxStok) {
                showToast("Stok maksimal " + maxStok, "error");
            }
        }

        // === 3. FUNGSI TOAST ===
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msg = document.getElementById('toast-message');
            const icon = document.getElementById('toast-icon');
            
            if(!toast) return;
            msg.innerText = message;
            toast.classList.remove('hidden');
            
            if(type === 'error'){
                icon.className = 'fa-solid fa-circle-exclamation text-red-500 text-lg';
                toast.classList.add('border-red-400');
            } else {
                icon.className = 'fa-solid fa-circle-check text-green-500 text-lg';
                toast.classList.remove('border-red-400');
            }

            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // === 4. SUBMIT CART ===
        async function submitCart(type) {
            let qty = qtyInput.value;
            let url = "{{ route('kiosk.add', $produk->id_produk) }}?qty=" + qty + "&type=" + type;
            
            if (type === 'now') { 
                window.location.href = url; 
                return; 
            }
            
            let btn = document.getElementById('btn-keranjang');
            let oriText = btn.innerHTML;
            btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i>";
            btn.disabled = true;

            try {
                let res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                let data = await res.json();
                
                if(data.status === 'success') {
                    showToast(data.message);
                    if(cartBadge) { 
                        cartBadge.innerText = data.total_cart; 
                        cartBadge.style.display='flex'; 
                    }
                } else { 
                    showToast(data.message, 'error'); 
                }
            } catch(e) { 
                showToast("Gagal koneksi", 'error'); 
            }
            
            btn.innerHTML = oriText;
            btn.disabled = false;
        }

        @if(session('success')) showToast("{{ session('success') }}", "success"); @endif
        @if(session('error')) showToast("{{ session('error') }}", "error"); @endif
    </script>
</body>

</html>