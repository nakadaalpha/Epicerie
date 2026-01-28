<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Pelanggan') - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        .modal {
            transition: opacity 0.25s ease;
        }

        body.modal-active {
            overflow-x: hidden;
            overflow-y: hidden !important;
        }

        @keyframes popIn {
            0% {
                transform: translate(-50%, -50%) scale(0.9);
                opacity: 0;
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        .toast-center {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            z-index: 9999;
        }

        /* Custom Scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles') {{-- Tempat untuk CSS tambahan per halaman --}}
</head>

<body class="bg-gray-50 text-gray-700 pb-20 font-sans">

    @include('partials.navbar-kiosk')

    {{-- GLOBAL TOAST NOTIFICATION --}}
    @if(session('success'))
    <div id="toast" class="toast-center bg-gray-900/95 text-white px-8 py-6 rounded-2xl shadow-2xl flex flex-col items-center gap-3 backdrop-blur-sm min-w-[300px]">
        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-xl shadow-lg shadow-blue-500/30"><i class="fa-solid fa-check"></i></div>
        <h3 class="font-bold text-lg">Berhasil!</h3>
        <p class="text-gray-300 text-sm">{{ session('success') }}</p>
    </div>
    <script>
        setTimeout(() => {
            const t = document.getElementById('toast');
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 300);
        }, 2000);
    </script>
    @endif

    <div class="max-w-[1100px] mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">

        {{-- === SIDEBAR UTAMA (GLOBAL) === --}}
        <div class="w-full md:w-[300px] shrink-0">
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm sticky top-24">

                {{-- FOTO PROFIL --}}
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden mb-5 flex items-center justify-center relative group border border-gray-100">
                    @if(Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300 font-bold text-7xl">{{ substr(Auth::user()->nama, 0, 1) }}</div>
                    @endif

                    {{-- Overlay Ganti Foto --}}
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center cursor-pointer" onclick="document.getElementById('globalFotoInput').click()">
                        <i class="fa-solid fa-camera text-white text-3xl drop-shadow-md"></i>
                    </div>
                </div>

                {{-- Form Hidden Ganti Foto --}}
                <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="foto_profil" id="globalFotoInput" class="hidden" onchange="this.form.submit()">
                </form>

                <div class="text-center mb-6">
                    <h3 class="font-bold text-gray-800 text-lg">{{ Auth::user()->nama }}</h3>
                    <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                </div>

                {{-- TOMBOL KARTU MEMBER --}}
                <div class="mb-6">
                    <button onclick="openGlobalCardModal()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30">
                        <i class="fa-solid fa-id-card"></i> Lihat Kartu Member
                    </button>
                    @if(Auth::user()->status_cetak_kartu == 'pending')
                    <p class="text-[10px] text-yellow-600 text-center mt-2 bg-yellow-50 py-1 rounded border border-yellow-100"><i class="fa-solid fa-clock mr-1"></i> Request cetak diproses</p>
                    @endif
                </div>

                {{-- NAVIGASI SIDEBAR --}}
                <div class="border-t border-gray-100 pt-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Menu Akun</p>
                    <nav class="space-y-1">
                        {{-- Link Profil --}}
                        <a href="{{ route('kiosk.profile') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-bold {{ request()->routeIs('kiosk.profile') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium' }}">
                            <div class="w-6 text-center"><i class="fa-regular fa-user"></i></div> Biodata Diri
                        </a>

                        {{-- Link Riwayat --}}
                        <a href="{{ route('kiosk.riwayat') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-bold {{ request()->routeIs('kiosk.riwayat') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium' }}">
                            <div class="w-6 text-center"><i class="fa-solid fa-clock-rotate-left"></i></div> Riwayat Transaksi
                        </a>

                        {{-- Link Ulasan --}}
                        <a href="{{ route('kiosk.ulasan') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-bold {{ request()->routeIs('kiosk.ulasan') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium' }}">
                            <div class="w-6 text-center"><i class="fa-solid fa-star"></i></div> Ulasan Saya
                        </a>

                        {{-- Tombol Logout --}}
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

        {{-- === KONTEN DINAMIS (YIELD) === --}}
        <div class="flex-1">
            @yield('content')
        </div>

    </div>

    {{-- MODAL KARTU MEMBER (GLOBAL) --}}
    <div id="globalCardModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-4 transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 flex justify-between items-center">
                <h3 class="font-extrabold text-white text-lg">Kartu Member Digital</h3>
                <button onclick="closeGlobalCardModal()" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white hover:bg-white hover:text-indigo-600 transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 bg-gray-50 flex justify-center">
                <div class="relative w-[342px] h-[216px] rounded-xl shadow-2xl overflow-hidden bg-[#050505] text-white">
                    <img src="{{ asset('images/card_bg.png') }}" class="absolute inset-0 w-full h-full object-cover z-0" onerror="this.style.display='none';">
                    <div class="relative z-10 w-full h-full p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <h1 class="font-extrabold text-2xl tracking-widest uppercase drop-shadow-md">ÉPICERIE</h1>
                            <div class="border border-[#2dd4bf] bg-black/40 backdrop-blur-sm rounded px-2 py-1">
                                <p class="text-[8px] font-bold text-[#2dd4bf] uppercase tracking-widest">{{ Auth::user()->membership }} MEMBER</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="font-bold text-lg uppercase leading-tight drop-shadow-md">{{ Str::limit(Auth::user()->nama, 18) }}</p>
                                <p class="text-[9px] text-[#94a3b8] font-mono tracking-widest">{{ Auth::user()->username }}</p>
                            </div>
                            <div class="bg-white p-1 rounded shadow-lg">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(40)->margin(0)->generate(Auth::user()->id_user) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GLOBAL SCRIPT --}}
    <script>
        function openGlobalCardModal() {
            document.getElementById('globalCardModal').classList.remove('hidden');
        }

        function closeGlobalCardModal() {
            document.getElementById('globalCardModal').classList.add('hidden');
        }
        // Menutup modal jika klik di luar area kartu
        document.getElementById('globalCardModal').addEventListener('click', function(e) {
            if (e.target === this) closeGlobalCardModal();
        });
    </script>

    @stack('scripts') {{-- Tempat untuk Script JS tambahan per halaman --}}

</body>

</html>