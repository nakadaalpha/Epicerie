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

        /* Animasi Toast */
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
    </style>
</head>

<body class="bg-white font-sans text-gray-800 min-h-screen flex flex-col">

    <div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[999] hidden flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300" role="alert">
        <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
            <i id="toast-icon" class="fa-solid text-lg"></i>
        </div>
        <div class="ml-3 text-sm font-bold text-gray-800" id="toast-message">Pesan Notifikasi</div>
    </div>

    @include('partials.navbar-kiosk')

    <main class="flex-grow max-w-[1200px] mx-auto w-full px-4 py-6">

        <nav class="flex items-center gap-2 text-sm text-gray-500 mt-5 mb-1 overflow-x-auto whitespace-nowrap pb-2 border-b border-transparent">
            <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold hover:text-blue-800 transition">Beranda</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @if($produk->kategori)
            <a href="{{ route('kiosk.index', ['kategori' => $produk->id_kategori]) }}" class="text-blue-600 font-bold hover:text-blue-800 transition">
                {{ $produk->kategori->nama_kategori }}
            </a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @endif
            <span class="text-gray-600 truncate font-medium max-w-[200px] md:max-w-md cursor-default" title="{{ $produk->nama_produk }}">
                {{ $produk->nama_produk }}
            </span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 relative mt-6">

            <div class="lg:col-span-4">
                <div class="sticky top-24">
                    <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200 mb-4 cursor-zoom-in relative group shadow-sm">
                        @if($produk->persen_diskon > 0)
                        <div class="absolute top-3 left-3 z-10 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md flex items-center gap-1">
                            <i class="fa-solid fa-tags"></i> Hemat {{ $produk->persen_diskon }}%
                        </div>
                        @endif

                        @if($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-contain p-4 transition duration-500 group-hover:scale-105">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-blue-100"><i class="fa-solid fa-image text-6xl"></i></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-snug mb-2">{{ $produk->nama_produk }}</h1>

                    <div class="flex items-center gap-3 text-sm mb-4">
                        <span class="text-gray-900 font-bold">Terjual {{ $produk->terjual ?? 0 }}</span>
                    </div>
                    @if($produk->persen_diskon > 0)
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm text-gray-400 line-through decoration-red-400 decoration-2">
                            Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}
                        </span>
                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                            Diskon {{ $produk->persen_diskon }}%
                        </span>
                    </div>
                    <h2 class="text-4xl font-extrabold text-blue-600">
                        Rp{{ number_format($produk->harga_final, 0, ',', '.') }}
                    </h2>
                    @else
                    <h2 class="text-4xl font-extrabold text-blue-600">
                        Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}
                    </h2>
                    @endif
                </div>

                <hr class="border-gray-100 my-2">

                <div class="space-y-2">
                    <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed text-justify">
                        <h3 class="font-bold text-gray-900 text-lg">Deskripsi Produk</h3>
                        <p>{{ $produk->deskripsi_produk ?? 'Tidak ada deskripsi yang tersedia untuk produk ini.' }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="sticky-card bg-white border border-gray-200 rounded-xl p-5 shadow-lg">
                    <h3 class="font-bold text-gray-900 mb-4">Atur jumlah dan catatan</h3>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center border border-gray-300 rounded-lg p-1">
                            <button onclick="updateQty(-1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 font-bold transition">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input type="text" id="qtyInput" value="1" class="w-10 text-center font-bold text-gray-700 outline-none text-sm bg-transparent" readonly>
                            <button onclick="updateQty(1)" class="w-7 h-7 flex items-center justify-center text-blue-600 hover:text-blue-700 font-bold transition">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">Stok: <span class="font-bold text-gray-800">{{ $produk->stok }}</span></span>
                    </div>

                    <div class="flex justify-between items-center mb-5">
                        <span class="text-gray-500 text-sm">Subtotal</span>
                        <span class="font-bold text-lg text-gray-900" id="subtotalDisplay">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</span>
                    </div>

                    @if($produk->stok > 0)
                    <div class="flex flex-col gap-3">
                        <button onclick="submitCart('cart')" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-center transition shadow-lg hover:shadow-blue-500/30">
                            <i class="fa-solid fa-plus mr-2"></i> Keranjang
                        </button>
                        <button onclick="submitCart('now')" class="block w-full border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-3 rounded-lg transition">
                            Beli Langsung
                        </button>
                    </div>
                    @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed">Stok Habis</button>
                    @endif
                </div>
            </div>

        </div>

        @if(isset($produkLain) && count($produkLain) > 0)
        <div class="mt-20 border-t border-gray-100 pt-10">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-extrabold text-gray-900">Pilihan Lainnya</h3>
                <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold text-sm hover:underline">Lihat Semua</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($produkLain as $rek)
                @php $hasDiskonRek = $rek->persen_diskon > 0; @endphp

                <a href="{{ route('produk.show', $rek->id_produk) }}" class="bg-white p-3 rounded-xl border border-gray-200 hover:shadow-lg hover:border-blue-300 transition duration-300 group flex flex-col justify-between h-full relative">

                    @if($hasDiskonRek)
                    <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">
                        -{{ $rek->persen_diskon }}%
                    </div>
                    @endif

                    <div class="aspect-square flex items-center justify-center overflow-hidden rounded-lg mb-3">
                        @if($rek->gambar)
                        <img src="{{ asset('storage/' . $rek->gambar) }}" class="w-full h-full object-contain p-2 group-hover:scale-110 transition duration-300">
                        @else <span class="text-4xl">📦</span> @endif
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-700 text-xs leading-snug mb-1 line-clamp-2 h-8">{{ $rek->nama_produk }}</h4>

                        @if($hasDiskonRek)
                        <div class="flex flex-col items-start">
                            <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                Rp{{ number_format($rek->harga_produk, 0, ',', '.') }}
                            </span>
                            <span class="text-blue-600 font-extrabold text-sm">
                                Rp{{ number_format($rek->harga_final, 0, ',', '.') }}
                            </span>
                        </div>
                        @else
                        <p class="text-blue-600 font-extrabold text-sm">Rp{{ number_format($rek->harga_produk, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </main>

    @include('partials.footer-kiosk')

    <script>
        // === VARIABEL PENTING (DIAMBIL DARI PHP) ===
        // Kita gunakan harga_final (setelah diskon) untuk kalkulasi JS
        const hargaSatuan = {
            {
                $produk - > harga_final
            }
        };
        const maxStok = {
            {
                $produk - > stok
            }
        };

        const qtyInput = document.getElementById('qtyInput');
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const cartBadge = document.getElementById('cart-badge');

        // Format Rupiah
        function formatRupiah(angka) {
            return 'Rp' + new Intl.NumberFormat('id-ID').format(angka);
        }

        // Update Jumlah & Subtotal
        function updateQty(change) {
            let currentQty = parseInt(qtyInput.value) || 1;
            let newQty = currentQty + change;

            if (newQty >= 1 && newQty <= maxStok) {
                qtyInput.value = newQty;
                // Kalkulasi Real-time
                subtotalDisplay.innerText = formatRupiah(newQty * hargaSatuan);
            }
        }

        // Fungsi Toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msg = document.getElementById('toast-message');
            const iconContainer = document.getElementById('toast-icon-container');
            const icon = document.getElementById('toast-icon');

            msg.innerText = message;
            toast.classList.remove('hidden', 'border-green-500', 'border-red-500');
            iconContainer.classList.remove('bg-green-100', 'text-green-500', 'bg-red-100', 'text-red-500');
            icon.classList.remove('fa-circle-check', 'fa-circle-xmark');

            if (type === 'success') {
                toast.classList.add('border-green-500');
                iconContainer.classList.add('bg-green-100', 'text-green-500');
                icon.classList.add('fa-circle-check');
            } else {
                toast.classList.add('border-red-500');
                iconContainer.classList.add('bg-red-100', 'text-red-500');
                icon.classList.add('fa-circle-xmark');
            }

            toast.classList.add('toast-enter');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // Cek Flash Message PHP
        @if(session('success')) showToast("{{ session('success') }}", "success");
        @endif
        @if(session('error')) showToast("{{ session('error') }}", "error");
        @endif

        // Submit Keranjang
        async function submitCart(type) {
            let qty = qtyInput.value;
            if (qty < 1) {
                showToast("Jumlah minimal 1", "error");
                return;
            }

            // URL Endpoint
            let url = "{{ route('kiosk.add', $produk->id_produk) }}?qty=" + qty + "&type=" + type;

            if (type === 'now') {
                window.location.href = url;
                return;
            }

            // AJAX Request
            try {
                let btn = document.querySelector("button[onclick=\"submitCart('cart')\"]");
                let originalText = btn.innerHTML;
                btn.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Menambahkan...";
                btn.disabled = true;

                let response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });
                let data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, "success");
                    if (cartBadge) {
                        cartBadge.innerText = data.total_cart;
                        cartBadge.style.display = 'flex';
                        cartBadge.classList.add('scale-125');
                        setTimeout(() => cartBadge.classList.remove('scale-125'), 200);
                    }
                } else if (data.redirect) {
                    window.location.href = data.redirect; // Redirect login jika belum login
                } else {
                    showToast(data.message || "Gagal", "error");
                }

                btn.innerHTML = originalText;
                btn.disabled = false;
            } catch (error) {
                console.error(error);
                showToast("Terjadi kesalahan sistem", "error");
            }
        }
    </script>
</body>

</html>

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
    </style>
</head>

<body class="bg-white font-sans text-gray-800 min-h-screen flex flex-col">

    <div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[999] hidden flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300" role="alert">
        <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
            <i id="toast-icon" class="fa-solid text-lg"></i>
        </div>
        <div class="ml-3 text-sm font-bold text-gray-800" id="toast-message">Pesan Notifikasi</div>
    </div>
    <style>
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
    </style>

    @include('partials.navbar-kiosk')

    <main class="flex-grow max-w-[1200px] mx-auto w-full px-4 py-6">

        <nav class="flex items-center gap-2 text-sm text-gray-500 mt-5 mb-1 overflow-x-auto whitespace-nowrap pb-2 border-b border-transparent">
            <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold hover:text-blue-800 transition">Beranda</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @if($produk->kategori)
            <a href="{{ route('kiosk.index', ['kategori' => $produk->id_kategori]) }}" class="text-blue-600 font-bold hover:text-blue-800 transition">
                {{ $produk->kategori->nama_kategori }}
            </a>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
            @endif
            <span class="text-gray-600 truncate font-medium max-w-[200px] md:max-w-md cursor-default" title="{{ $produk->nama_produk }}">
                {{ $produk->nama_produk }}
            </span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 relative">

            <div class="lg:col-span-4">
                <div class="sticky top-24">
                    <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200 mb-4 cursor-zoom-in relative group shadow-sm">
                        @if($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-contain p-4 transition duration-500 group-hover:scale-105">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-blue-100"><i class="fa-solid fa-image text-6xl"></i></div>
                        @endif
                    </div>
                    <div class="flex gap-3 overflow-x-auto">
                        <div class="w-16 h-16 border-2 border-blue-600 rounded-lg p-1 bg-white cursor-pointer overflow-hidden">
                            @if($produk->gambar) <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover rounded"> @endif
                        </div>
                        <div class="w-16 h-16 border border-gray-200 rounded-lg bg-gray-50 hover:border-blue-300 transition"></div>
                        <div class="w-16 h-16 border border-gray-200 rounded-lg bg-gray-50 hover:border-blue-300 transition"></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 leading-snug mb-2">{{ $produk->nama_produk }}</h1>
                    <div class="flex items-center gap-2 text-sm mb-4">
                        <span class="font-bold text-gray-900">Terjual 100+</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-yellow-500 font-bold"><i class="fa-solid fa-star"></i> 4.9</span>
                        <span class="text-gray-400">(25 rating)</span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-blue-600">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</h2>
                </div>
                <hr class="border-gray-100 my-2">
                <div class="space-y-4">
                    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                        <p>{{ $produk->deskripsi_produk ?? 'Tidak ada deskripsi' }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="sticky-card bg-white border border-gray-200 rounded-xl p-5 shadow-lg">
                    <h3 class="font-bold text-gray-900 mb-4">Atur jumlah dan catatan</h3>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center border border-gray-300 rounded-lg p-1">
                            <button onclick="updateQty(-1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 font-bold transition">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input type="text" id="qtyInput" value="1" class="w-10 text-center font-bold text-gray-700 outline-none text-sm bg-transparent" readonly>
                            <button onclick="updateQty(1)" class="w-7 h-7 flex items-center justify-center text-blue-600 hover:text-blue-700 font-bold transition">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">Stok: <span class="font-bold text-gray-800">{{ $produk->stok }}</span></span>
                    </div>

                    <div class="flex justify-between items-center mb-5">
                        <span class="text-gray-500 text-sm">Subtotal</span>
                        <span class="font-bold text-lg text-gray-900" id="subtotalDisplay">Rp{{ number_format($produk->harga_produk, 0, ',', '.') }}</span>
                    </div>

                    @if($produk->stok > 0)
                    <div class="flex flex-col gap-3">
                        <button onclick="submitCart('cart')" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-center transition shadow-lg hover:shadow-blue-500/30">
                            <i class="fa-solid fa-plus mr-2"></i> Keranjang
                        </button>
                        <button onclick="submitCart('now')" class="block w-full border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-3 rounded-lg transition">
                            Beli Langsung
                        </button>
                    </div>
                    @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed">Stok Habis</button>
                    @endif
                </div>
            </div>

        </div>

        @if(isset($produkLain) && count($produkLain) > 0)
        <div class="mt-16 border-t border-gray-100 pt-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Pilihan lainnya untukmu</h3>
                <a href="#" class="text-blue-600 font-bold text-sm hover:text-blue-700">Lihat Semua</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($produkLain as $rek)
                <a href="{{ route('produk.show', $rek->id_produk) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-xl hover:border-blue-500 transition duration-300 group flex flex-col justify-between h-full relative">
                    <div class="aspect-square flex items-center justify-center overflow-hidden">
                        @if($rek->gambar)
                        <img src="{{ asset('storage/' . $rek->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                        @else <span class="text-4xl">📦</span> @endif
                    </div>
                    <div class="p-3">
                        <h4 class="font-medium text-gray-700 text-sm leading-snug mb-1 line-clamp-2 h-10">{{ $rek->nama_produk }}</h4>
                        <p class="text-gray-900 font-extrabold text-base mb-1">Rp{{ number_format($rek->harga_produk, 0, ',', '.') }}</p>
                        <div class="flex items-center gap-1 text-[10px] text-gray-500">
                            <i class="fa-solid fa-star text-yellow-400"></i>
                            <span class="text-gray-700 font-bold">4.9</span>
                        </div>
                    </div>
                    <div class="px-3 pb-3 mt-2">
                        <div class="block w-full border border-blue-600 text-blue-600 group-hover:bg-blue-600 group-hover:text-white text-xs font-bold py-1.5 rounded-lg text-center transition">+ Keranjang</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </main>

    <footer class="mt-20 border-t border-gray-100 py-10 bg-white">
        <div class="max-w-[1200px] mx-auto px-4 text-center text-gray-400 text-sm">&copy; 2025 Épicerie Kiosk System. All rights reserved.</div>
    </footer>

    <script>
        // === FUNGSI TOAST (Pop-up Keren) ===
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msg = document.getElementById('toast-message');
            const iconContainer = document.getElementById('toast-icon-container');
            const icon = document.getElementById('toast-icon');

            msg.innerText = message;
            toast.classList.remove('hidden', 'border-green-500', 'border-red-500');
            iconContainer.classList.remove('bg-green-100', 'text-green-500', 'bg-red-100', 'text-red-500');
            icon.classList.remove('fa-circle-check', 'fa-circle-xmark');

            if (type === 'success') {
                toast.classList.add('border-green-500');
                iconContainer.classList.add('bg-green-100', 'text-green-500');
                icon.classList.add('fa-circle-check');
            } else {
                toast.classList.add('border-red-500');
                iconContainer.classList.add('bg-red-100', 'text-red-500');
                icon.classList.add('fa-circle-xmark');
            }

            toast.classList.add('toast-enter');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // === CEK FLASH MESSAGE DARI CONTROLLER (Kalo ada redirect) ===
        @if(session('success')) showToast("{{ session('success') }}", "success");
        @endif
        @if(session('error')) showToast("{{ session('error') }}", "error");
        @endif

        // === VARIABEL & FUNGSI LAINNYA ===
        const hargaSatuan = {
            {
                $produk - > harga_produk
            }
        };
        const maxStok = {
            {
                $produk - > stok
            }
        };
        const qtyInput = document.getElementById('qtyInput');
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const cartBadge = document.getElementById('cart-badge');

        function formatRupiah(angka) {
            return 'Rp' + new Intl.NumberFormat('id-ID').format(angka);
        }

        function updateQty(change) {
            let currentQty = parseInt(qtyInput.value) || 1;
            let newQty = currentQty + change;
            if (newQty >= 1 && newQty <= maxStok) {
                qtyInput.value = newQty;
                subtotalDisplay.innerText = formatRupiah(newQty * hargaSatuan);
            }
        }

        async function submitCart(type) {
            let qty = qtyInput.value;
            if (qty < 1) {
                showToast("Jumlah minimal 1", "error");
                return;
            }

            let url = "{{ route('kiosk.add', $produk->id_produk) }}?qty=" + qty + "&type=" + type;

            if (type === 'now') {
                window.location.href = url;
                return;
            }

            // LOGIKA TAMBAH KERANJANG (AJAX)
            try {
                let btn = document.querySelector("button[onclick=\"submitCart('cart')\"]");
                let originalText = btn.innerHTML;
                btn.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i>";
                btn.disabled = true;

                let response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });
                let data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, "success"); // PAKE TOAST KEREN
                    if (cartBadge) {
                        cartBadge.innerText = data.total_cart;
                        cartBadge.style.display = 'flex';
                        cartBadge.classList.add('scale-125');
                        setTimeout(() => cartBadge.classList.remove('scale-125'), 200);
                    }
                } else {
                    showToast(data.message || "Gagal", "error");
                }
                btn.innerHTML = originalText;
                btn.disabled = false;
            } catch (error) {
                console.error(error);
                showToast("Koneksi Error", "error");
            }
        }
    </script>

</body>

</html>