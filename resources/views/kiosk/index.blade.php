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

        /* CSS untuk Tombol Slider */
        .slider-btn {
            opacity: 0;
            transition: all 0.3s ease-in-out;
            transform: scale(0.8);
        }

        /* Saat Hover di Container Slider, tombol muncul dan bergeser ke posisi ideal */
        #slider-container:hover .slider-btn-prev {
            opacity: 1;
            transform: scale(1);
        }

        #slider-container:hover .slider-btn-next {
            opacity: 1;
            transform:scale(1);
        }
    </style>
</head>

<body class="font-sans">

    @include('partials.navbar-kiosk')

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl relative shadow-sm">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
        </div>
    </div>
    @endif
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl relative shadow-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    </div>
    @endif

    @if(isset($sliders) && count($sliders) > 0)
    <div class="max-w-7xl mx-auto px-4 mt-6">
        <div id="slider-container" class="relative w-full rounded-2xl overflow-hidden shadow-md group aspect-[3/1] md:aspect-[3.5/1]">

            <div id="slider-track" class="flex h-full w-full">
                @foreach($sliders as $s)
                <div class="slider-item min-w-full h-full relative">
                    <img src="{{ asset('storage/' . $s->gambar) }}" class="w-full h-full object-cover" alt="{{ $s->judul }}">
                </div>
                @endforeach
            </div>

            <button id="prevBtn" class="slider-btn slider-btn-prev absolute left-0 top-1/2 -translate-y-1/2 bg-white/90 text-gray-700 hover:text-blue-600 w-10 h-10 rounded-full shadow-lg flex items-center justify-center z-10 -ml-4 group-hover:ml-0 cursor-pointer">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button id="nextBtn" class="slider-btn slider-btn-next absolute right-0 top-1/2 -translate-y-1/2 bg-white/90 text-gray-700 hover:text-blue-600 w-10 h-10 rounded-full shadow-lg flex items-center justify-center z-10 -mr-4 group-hover:mr-0 cursor-pointer">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                @foreach($sliders as $key => $s)
                <div class="slider-dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300 {{ $key == 0 ? 'w-6 bg-white' : '' }}"></div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    <div class="max-w-7xl mx-auto px-4 mt-6 space-y-8">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 text-white w-8 h-8 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Produk Terbaru</h2>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach($produkTerbaru as $p)
                @php
                $qty = $keranjangItems[$p->id_produk] ?? 0;
                $hasDiskon = $p->persen_diskon > 0;
                @endphp

                <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md hover:border-blue-200 relative group overflow-hidden">

                    <div class="absolute top-2 left-2 z-10 bg-blue-600 text-white text-[9px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-bolt"></i> NEW
                    </div>

                    @if($hasDiskon)
                    <div class="absolute top-2 right-2 z-10 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm">
                        -{{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                        <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>

                        @if($hasDiskon)
                        <div class="flex flex-col items-start mb-1">
                            <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            <span class="text-blue-600 font-extrabold text-sm block">
                                Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                            </span>
                        </div>
                        @else
                        <span class="text-blue-600 font-extrabold text-sm mb-1 block">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        @endif
                    </a>

                    <div class="flex justify-end mt-2 z-20 relative">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 hover:bg-blue-700 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @else
                        <span class="text-xs text-red-500 font-bold py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="bg-orange-500 text-white w-8 h-8 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-gray-800 text-lg leading-tight">Paling Laris</h2>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach($produkTerlaris as $index => $p)
                @php
                $qty = $keranjangItems[$p->id_produk] ?? 0;
                $hasDiskon = $p->persen_diskon > 0;
                @endphp

                <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md hover:border-orange-200 relative group overflow-hidden">
                    <span class="absolute -left-2 -bottom-4 text-7xl font-black text-gray-100 italic select-none pointer-events-none z-0">#{{ $index + 1 }}</span>

                    @if($hasDiskon)
                    <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-tags"></i> Hemat {{ $p->persen_diskon }}%
                    </div>
                    @endif

                    <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                        <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($p->gambar)
                            <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                            @else
                            <span class="text-4xl">📦</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>

                        @if($hasDiskon)
                        <div class="flex flex-col items-start mb-1">
                            <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            <span class="text-orange-600 font-extrabold text-sm block">
                                Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                            </span>
                        </div>
                        @else
                        <span class="text-orange-600 font-extrabold text-sm mb-1 block">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        @endif
                    </a>

                    <div class="flex justify-end mt-2 z-20 relative">
                        @if($p->stok > 0)
                        <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-orange-500 hover:bg-orange-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                        @else
                        <span class="text-xs text-red-500 font-bold py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 pb-24 mt-8">
        <div class="flex justify-between items-center mb-4 sticky top-[70px] backdrop-blur-sm py-3 z-30">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="font-extrabold text-gray-700 text-lg">Semua Produk</h2>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
            @forelse($produk as $p)
            @php
            $qty = $keranjangItems[$p->id_produk] ?? 0;
            // Akses accessor model Produk
            $hasDiskon = $p->persen_diskon > 0;
            @endphp

            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md hover:border-blue-200 relative group">

                @if($hasDiskon)
                <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                    <i class="fa-solid fa-tags"></i> Hemat {{ $p->persen_diskon }}%
                </div>
                @endif

                <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                    <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                        @else
                        <span class="text-4xl">📦</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>

                    @if($hasDiskon)
                    <div class="flex flex-col items-start mb-1">
                        <span class="text-xs text-gray-400 line-through decoration-red-400">
                            Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                        </span>
                        <span class="text-blue-600 font-bold text-sm block">
                            Rp{{ number_format($p->harga_final, 0, ',', '.') }}
                        </span>
                    </div>
                    @else
                    <span class="text-blue-600 font-bold text-sm mb-1 block">
                        Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                    </span>
                    @endif
                </a>

                <div class="flex justify-end mt-2 z-20 relative">
                    @if($p->stok > 0)
                    <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    @else
                    <span class="text-xs text-red-500 font-bold mb-1 py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-bold">Produk tidak ditemukan.</p>
                <p class="text-xs text-gray-400">Coba kata kunci lain atau reset filter.</p>
            </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function(event) {

            // --- LOGIKA INFINITE SLIDER ---
            const track = document.getElementById('slider-track');
            const items = document.querySelectorAll('.slider-item');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const dots = document.querySelectorAll('.slider-dot');

            if (items.length > 0) {
                let currentIndex = 1; // Mulai dari 1 karena kita akan clone
                const totalSlides = items.length;
                let isTransitioning = false;
                let autoSlideInterval;

                // Clone elemen pertama dan terakhir
                const firstClone = items[0].cloneNode(true);
                const lastClone = items[totalSlides - 1].cloneNode(true);

                track.appendChild(firstClone);
                track.insertBefore(lastClone, items[0]);

                const allSlides = document.querySelectorAll('.slider-item'); // List baru termasuk clone

                // Set posisi awal (menampilkan slide asli pertama)
                track.style.transform = `translateX(-100%)`;

                function updateSlider(index, withTransition = true) {
                    if (withTransition) {
                        track.style.transition = 'transform 0.5s ease-in-out';
                    } else {
                        track.style.transition = 'none';
                    }
                    track.style.transform = `translateX(-${index * 100}%)`;

                    // Update Dots (Handle logic untuk index clone)
                    let dotIndex = index - 1;
                    if (index === 0) dotIndex = totalSlides - 1;
                    if (index === totalSlides + 1) dotIndex = 0;

                    dots.forEach((dot, i) => {
                        if (i === dotIndex) {
                            dot.classList.add('w-6', 'bg-white');
                            dot.classList.remove('bg-white/50');
                        } else {
                            dot.classList.remove('w-6', 'bg-white');
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

                // Event Listener untuk transisi selesai (Infinite Loop Logic)
                track.addEventListener('transitionend', () => {
                    isTransitioning = false;
                    if (currentIndex === 0) { // Jika di clone terakhir (kiri)
                        currentIndex = totalSlides;
                        updateSlider(currentIndex, false);
                    }
                    if (currentIndex === totalSlides + 1) { // Jika di clone pertama (kanan)
                        currentIndex = 1;
                        updateSlider(currentIndex, false);
                    }
                });

                // Button Listeners
                nextBtn.addEventListener('click', () => {
                    nextSlide();
                    resetAutoSlide();
                });
                prevBtn.addEventListener('click', () => {
                    prevSlide();
                    resetAutoSlide();
                });

                // Auto Slide
                function startAutoSlide() {
                    autoSlideInterval = setInterval(nextSlide, 4000); // 4 detik
                }

                function resetAutoSlide() {
                    clearInterval(autoSlideInterval);
                    startAutoSlide();
                }

                startAutoSlide();

                // Pause saat hover
                const container = document.getElementById('slider-container');
                container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                container.addEventListener('mouseleave', startAutoSlide);
            }

            // --- Scroll Position Retention ---
            var scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        });

        window.onbeforeunload = function(e) {
            sessionStorage.setItem('scrollpos', window.scrollY);
        };

        function closeQtyModal() {
            document.getElementById('modalQty').classList.add('hidden');
        }
    </script>

</body>

</html>