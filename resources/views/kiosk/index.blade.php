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

        .slider-btn {
            opacity: 0;
            transition: all 0.3s ease-in-out;
            transform: scale(0.8);
        }

        #slider-container:hover .slider-btn-prev,
        #slider-container:hover .slider-btn-next {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>

<body class="font-sans text-gray-800/50">

    @include('partials.navbar-kiosk')

    {{-- Notifikasi --}}
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl relative shadow-sm text-sm">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
        </div>
    </div>
    @endif
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl relative shadow-sm text-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    </div>
    @endif

    {{-- Slider --}}
    @if(isset($sliders) && count($sliders) > 0)
    <div class="max-w-7xl mx-auto px-4 mt-6">
        <div id="slider-container" class="relative w-full rounded-2xl overflow-hidden shadow-sm group aspect-[3/1] md:aspect-[3.5/1]">
            <div id="slider-track" class="flex h-full w-full">
                @foreach($sliders as $s)
                <div class="slider-item min-w-full h-full relative">
                    <img src="{{ isset($s->is_dummy) ? $s->gambar : asset('storage/' . $s->gambar) }}" class="w-full h-full object-cover" alt="{{ $s->judul }}">
                </div>
                @endforeach
            </div>
            <button id="prevBtn" class="absolute top-1/2 -translate-y-1/2 z-20 bg-white text-slate-500 hover:text-slate-800 w-9 h-9 rounded-full shadow-md flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 left-4 cursor-pointer slider-btn-prev">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button id="nextBtn" class="absolute top-1/2 -translate-y-1/2 z-20 bg-white text-slate-500 hover:text-slate-800 w-9 h-9 rounded-full shadow-md flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 right-4 cursor-pointer slider-btn-next">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1 z-10">
                @foreach($sliders as $key => $s)
                <div class="slider-dot w-1.5 h-1.5 rounded-full bg-white/40 backdrop-blur-sm transition-all duration-300 {{ $key == 0 ? '!bg-white w-4 shadow-sm' : '' }}"></div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 mt-6 space-y-10">

        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 text-white w-7 h-7 rounded-lg flex items-center justify-center shadow-blue-200 shadow-md">
                        <i class="fa-solid fa-clock text-xs"></i>
                    </div>
                    <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Produk Terbaru</h2>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($produkTerbaru as $p)
                @php
                $hasDiskon = $p->persen_diskon > 0;
                $avgRating = $p->ulasan->avg('rating') ?? 0;
                $countRating = $p->ulasan->count();
                $terjual = $p->total_terjual ?? 0;
                @endphp

                <div class="bg-white p-2 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between h-full relative overflow-hidden">

                    {{-- Image & Badge Wrapper --}}
                    <div class="relative mb-2">
                        @if($hasDiskon)
                        <div class="absolute top-0 left-0 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-sm">
                            -{{ $p->persen_diskon }}%
                        </div>
                        @else
                        <div class="absolute top-0 left-0 z-20 bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-sm">
                            NEW
                        </div>
                        @endif

                        <a href="{{ route('produk.show', $p->id_produk) }}" class="block aspect-square rounded-lg overflow-hidden">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-105 transition duration-300">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300"><i class="fa-solid fa-image"></i></div>
                            @endif
                        </a>
                    </div>

                    {{-- Info Produk --}}
                    <div class="flex flex-col flex-1">
                        <a href="{{ route('produk.show', $p->id_produk) }}" class="block mb-1">
                            <h3 class="font-bold text-gray-800 text-xs leading-snug line-clamp-2 h-8" title="{{ $p->nama_produk }}">
                                {{ $p->nama_produk }}
                            </h3>
                        </a>

                        {{-- Rating --}}
                        <div class="flex items-center gap-1 mb-1">
                            <i class="fa-solid fa-star text-[10px] text-yellow-400"></i>
                            <span class="text-[10px] text-gray-500">{{ number_format($avgRating, 1) }} <span class="text-gray-300">•</span> {{ $terjual }} terjual</span>
                        </div>

                        {{-- Harga --}}
                        <div class="mt-auto">
                            <div class="flex flex-wrap items-baseline gap-x-1.5">
                                <span class="text-sm font-extrabold text-blue-600">
                                    Rp{{ number_format($hasDiskon ? $p->harga_final : $p->harga_produk, 0, ',', '.') }}
                                </span>
                                @if($hasDiskon)
                                <span class="text-[10px] text-gray-400 line-through">
                                    Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="mt-2">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 rounded-lg text-center transition shadow-sm active:scale-95">
                            + Keranjang
                        </a>
                        @else
                        <button disabled class="block w-full bg-gray-100 text-gray-400 font-bold text-xs py-1.5 rounded-lg text-center cursor-not-allowed">
                            Habis
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="bg-orange-500 text-white w-7 h-7 rounded-lg flex items-center justify-center shadow-orange-200 shadow-md">
                    <i class="fa-solid fa-fire text-xs"></i>
                </div>
                <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Paling Laris</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($produkTerlaris as $index => $p)
                @php
                $hasDiskon = $p->persen_diskon > 0;
                $avgRating = $p->ulasan->avg('rating') ?? 0;
                $countRating = $p->ulasan->count();
                $terjual = $p->total_terjual ?? 0;
                @endphp

                <div class="bg-white p-2 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between h-full relative overflow-hidden">

                    {{-- Nomor Peringkat (Background) --}}
                    <!-- <span class="absolute -left-1 -top-3 text-[4.5rem] leading-none font-black text-gray-100 italic select-none pointer-events-none z-0">
                        #{{ $index + 1 }}
                    </span> -->

                    {{-- Image & Badge Wrapper --}}
                    <div class="relative mb-2 z-10">
                        @if($hasDiskon)
                        <div class="absolute top-0 right-0 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-bl-lg rounded-tr-lg shadow-sm">
                            -{{ $p->persen_diskon }}%
                        </div>
                        @endif

                        <a href="{{ route('produk.show', $p->id_produk) }}" class="block aspect-square/50 rounded-lg overflow-hidden">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-105 transition duration-300 mix-blend-multiply">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300"><i class="fa-solid fa-image"></i></div>
                            @endif
                        </a>
                    </div>

                    {{-- Info Produk --}}
                    <div class="flex flex-col flex-1 z-10">
                        <a href="{{ route('produk.show', $p->id_produk) }}" class="block mb-1">
                            <h3 class="font-bold text-gray-800 text-xs leading-snug line-clamp-2 h-8" title="{{ $p->nama_produk }}">
                                {{ $p->nama_produk }}
                            </h3>
                        </a>

                        <div class="flex items-center gap-1 mb-1">
                            <i class="fa-solid fa-star text-[10px] text-yellow-400"></i>
                            <span class="text-[10px] text-gray-500">{{ number_format($avgRating, 1) }} <span class="text-gray-300">•</span> {{ $terjual }} terjual</span>
                        </div>

                        <div class="mt-auto">
                            <div class="flex flex-wrap items-baseline gap-x-1.5">
                                <span class="text-sm font-extrabold text-orange-600">
                                    Rp{{ number_format($hasDiskon ? $p->harga_final : $p->harga_produk, 0, ',', '.') }}
                                </span>
                                @if($hasDiskon)
                                <span class="text-[10px] text-gray-400 line-through">
                                    Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <span class="absolute right-1 bottom-5 text-[5rem] leading-none font-black text-gray-100 italic select-none pointer-events-none z-10">
                            #{{ $index + 1 }}
                        </span>
                    </div>


                    {{-- Tombol --}}
                    <div class="mt-2 z-10">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="block w-full bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs py-1.5 rounded-lg text-center transition shadow-sm active:scale-95">
                            + Keranjang
                        </a>
                        @else
                        <button disabled class="block w-full bg-gray-100 text-gray-400 font-bold text-xs py-1.5 rounded-lg text-center cursor-not-allowed">
                            Habis
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 pb-24 mt-8">
        <div class="flex justify-between items-center mb-6 sticky top-[70px] backdrop-blur-md py-2 z-30 bg-white/90 rounded-lg px-3 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2">
                <div class="w-1 h-5 bg-blue-600 rounded-full"></div>
                <h2 class="font-extrabold text-gray-700 text-base">Semua Produk</h2>
            </div>
        </div>

        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            @forelse($produk as $p)
            @php
            $hasDiskon = $p->persen_diskon > 0;
            $avgRating = $p->ulasan->avg('rating') ?? 0;
            $countRating = $p->ulasan->count();
            $terjual = $p->total_terjual ?? 0;
            @endphp

            <div class="bg-white p-2 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between h-full relative overflow-hidden">

                <div class="relative mb-2">
                    @if($hasDiskon)
                    <div class="absolute top-0 left-0 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-sm">
                        -{{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block aspect-square rounded-lg overflow-hidden">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-3 group-hover:scale-105 transition duration-300">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-3xl text-gray-300"><i class="fa-solid fa-image"></i></div>
                        @endif
                    </a>
                </div>

                <div class="flex flex-col flex-1">
                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block mb-1">
                        <h3 class="font-bold text-gray-800 text-xs leading-snug line-clamp-2 h-8" title="{{ $p->nama_produk }}">
                            {{ $p->nama_produk }}
                        </h3>
                    </a>

                    <div class="flex items-center gap-1 mb-1">
                        <i class="fa-solid fa-star text-[10px] text-yellow-400"></i>
                        <span class="text-[10px] text-gray-500">{{ number_format($avgRating, 1) }} <span class="text-gray-300">•</span> {{ $terjual }} terjual</span>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap items-baseline gap-x-1.5">
                            <span class="text-sm font-extrabold text-blue-600">
                                Rp{{ number_format($hasDiskon ? $p->harga_final : $p->harga_produk, 0, ',', '.') }}
                            </span>
                            @if($hasDiskon)
                            <span class="text-[10px] text-gray-400 line-through">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    @if($p->stok > 0)
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 rounded-lg text-center transition shadow-sm active:scale-95">
                        + Keranjang
                    </a>
                    @else
                    <button disabled class="block w-full bg-gray-100 text-gray-400 font-bold text-xs py-1.5 rounded-lg text-center cursor-not-allowed">
                        Habis
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="inline-block p-4 rounded-full mb-3">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-bold">Produk tidak ditemukan.</p>
                <p class="text-xs text-gray-400">Coba kata kunci lain atau reset filter.</p>
            </div>
            @endforelse
        </div>

        @if($produk->hasMorePages())
        <div class="mt-8 flex justify-center">
            <button id="loadMoreBtn" data-url="{{ $produk->nextPageUrl() }}" onclick="loadMoreProducts()" class="bg-white border border-gray-300 text-gray-600 font-bold py-2.5 px-6 rounded-full shadow-sm hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all flex items-center gap-2 group text-sm">
                <span id="btnText">Muat Lebih Banyak</span>
                <i id="btnIcon" class="fa-solid fa-chevron-down group-hover:translate-y-1 transition-transform"></i>
                <i id="btnSpinner" class="fa-solid fa-circle-notch fa-spin hidden"></i>
            </button>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            // ... (Kode Javascript Slider & ScrollPos tetap sama, tidak perlu diubah) ...
            const track = document.getElementById('slider-track');
            const items = document.querySelectorAll('.slider-item');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const dots = document.querySelectorAll('.slider-dot');

            if (items.length > 0) {
                let currentIndex = 1;
                const totalSlides = items.length;
                let isTransitioning = false;
                let autoSlideInterval;
                const firstClone = items[0].cloneNode(true);
                const lastClone = items[totalSlides - 1].cloneNode(true);
                track.appendChild(firstClone);
                track.insertBefore(lastClone, items[0]);
                track.style.transform = `translateX(-100%)`;

                function updateSlider(index, withTransition = true) {
                    if (withTransition) track.style.transition = 'transform 0.5s ease-in-out';
                    else track.style.transition = 'none';
                    track.style.transform = `translateX(-${index * 100}%)`;

                    let dotIndex = index - 1;
                    if (index === 0) dotIndex = totalSlides - 1;
                    if (index === totalSlides + 1) dotIndex = 0;
                    dots.forEach((dot, i) => {
                        if (i === dotIndex) {
                            dot.classList.add('w-4', 'bg-white');
                            dot.classList.remove('bg-white/50');
                        } else {
                            dot.classList.remove('w-4', 'bg-white');
                            dot.classList.add('bg-white/50');
                        }
                    });
                }

                function nextSlide() {
                    if (isTransitioning) return;
                    isTransitioning = true;
                    currentIndex++;
                    updateSlider(currentIndex);
                }

                function prevSlide() {
                    if (isTransitioning) return;
                    isTransitioning = true;
                    currentIndex--;
                    updateSlider(currentIndex);
                }
                track.addEventListener('transitionend', () => {
                    isTransitioning = false;
                    if (currentIndex === 0) {
                        currentIndex = totalSlides;
                        updateSlider(currentIndex, false);
                    }
                    if (currentIndex === totalSlides + 1) {
                        currentIndex = 1;
                        updateSlider(currentIndex, false);
                    }
                });
                nextBtn.addEventListener('click', () => {
                    nextSlide();
                    resetAutoSlide();
                });
                prevBtn.addEventListener('click', () => {
                    prevSlide();
                    resetAutoSlide();
                });

                function startAutoSlide() {
                    autoSlideInterval = setInterval(nextSlide, 4000);
                }

                function resetAutoSlide() {
                    clearInterval(autoSlideInterval);
                    startAutoSlide();
                }
                startAutoSlide();
                const container = document.getElementById('slider-container');
                container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                container.addEventListener('mouseleave', startAutoSlide);
            }
        });

        // LOAD MORE LOGIC (Tetap sama)
        function loadMoreProducts() {
            const btn = document.getElementById('loadMoreBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            const btnSpinner = document.getElementById('btnSpinner');
            const url = btn.getAttribute('data-url');
            if (!url) return;
            btn.disabled = true;
            btnText.innerText = 'Memuat...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            fetch(url).then(response => response.text()).then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newItems = doc.querySelectorAll('#product-grid > div');
                const grid = document.getElementById('product-grid');
                newItems.forEach(item => {
                    item.classList.add('opacity-0', 'translate-y-4');
                    grid.appendChild(item);
                    setTimeout(() => {
                        item.classList.remove('opacity-0', 'translate-y-4');
                    }, 50);
                });
                const newBtn = doc.getElementById('loadMoreBtn');
                if (newBtn) {
                    btn.setAttribute('data-url', newBtn.getAttribute('data-url'));
                    btn.disabled = false;
                    btnText.innerText = 'Muat Lebih Banyak';
                    btnIcon.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                } else {
                    btn.remove();
                }
            }).catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btnText.innerText = 'Coba Lagi';
                btnSpinner.classList.add('hidden');
            });
        }
    </script>
</body>

</html>