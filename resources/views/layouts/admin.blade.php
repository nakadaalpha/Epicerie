<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        .hide-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 h-screen flex overflow-hidden font-sans">

    <aside id="sidebar" class="bg-white w-64 flex-shrink-0 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 fixed md:relative z-50 h-full shadow-[4px_0_24px_rgba(0,0,0,0.05)] border-r border-white/20">

        <div class="h-20 flex items-center px-6 border-b border-gray-100 shrink-0">
            <a href="{{ route('dashboard') }}" class="text-2xl font-extrabold text-blue-600 tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-store"></i> ÈPICERIE
            </a>
            <button onclick="toggleSidebar()" class="md:hidden ml-auto text-gray-400 hover:text-red-500">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto hide-scroll p-4 space-y-1.5">

            <a href="{{ route('transaksi.index') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 mb-6 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 rounded-xl shadow-lg shadow-blue-200 transition-all group transform hover:scale-[1.02]">
                <i class="fa-solid fa-plus transition-transform group-hover:rotate-90"></i>
                <span>Pesanan Baru</span>
            </a>

            <p class="px-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2 mt-2">Menu Utama</p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i> Dashboard
            </a>

            <a href="{{ route('transaksi.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('transaksi*') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-solid fa-receipt w-5 text-center {{ request()->routeIs('transaksi*') ? 'text-blue-600' : 'text-gray-400' }}"></i> Transaksi
            </a>

            <a href="{{ route('inventaris.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('inventaris*') || request()->routeIs('produk*') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-solid fa-box w-5 text-center {{ request()->routeIs('inventaris*') ? 'text-blue-600' : 'text-gray-400' }}"></i> Inventaris
            </a>

            <a href="{{ route('kategori.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('kategori*') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-solid fa-tags w-5 text-center {{ request()->routeIs('kategori*') ? 'text-blue-600' : 'text-gray-400' }}"></i> Kategori
            </a>

            <a href="{{ route('slider.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('slider*') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-regular fa-images w-5 text-center {{ request()->routeIs('slider*') ? 'text-blue-600' : 'text-gray-400' }}"></i> Slider
            </a>

            <a href="{{ route('laporan.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('laporan*') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                <i class="fa-solid fa-file-invoice w-5 text-center {{ request()->routeIs('laporan*') ? 'text-blue-600' : 'text-gray-400' }}"></i> Laporan
            </a>

        </nav>

        <div class="border-t border-gray-100 p-4 shrink-0 relative bg-white z-50">

            <button onclick="toggleUserMenu()" class="flex items-center gap-3 w-full px-2 py-2 rounded-xl hover:bg-gray-50/50 hover:shadow-sm hover:border-gray-200 border border-transparent transition-all text-left group">
                @if(Auth::check() && Auth::user()->foto_profil)
                <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                @else
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm">
                    {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                </div>
                @endif

                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->nama ?? 'User' }}</p>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide truncate">{{ Auth::user()->role ?? 'Admin' }}</p>
                </div>

                <i class="fa-solid text-gray-400 text-xs group-hover:text-blue-600 transition"></i>
            </button>

            <div id="userMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden z-[60] transform transition-all duration-200 origin-bottom">
                <a href="{{ route('kiosk.index') }}" class="block px-5 py-3 text-sm font-bold text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition">
                    <i class="fa-solid fa-store w-6 text-center text-gray-400"></i> Lihat Toko
                </a>
                <a href="{{ route('kiosk.profile') }}" class="block px-5 py-3 text-sm font-bold text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition">
                    <i class="fa-solid fa-gear w-6 text-center text-gray-400"></i> Pengaturan
                </a>
                <div class="border-t border-gray-50"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-5 py-3 text-sm font-bold text-red-500 hover:bg-red-50 transition flex items-center">
                        <i class="fa-solid fa-arrow-right-from-bracket w-6 text-center mr-1"></i> Keluar
                    </button>
                </form>
            </div>

        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">

        <header class="md:hidden h-16 flex items-center justify-between px-6 shrink-0 z-30">
            <span class="font-extrabold text-white text-lg drop-shadow-md">@yield('header_title', 'Dashboard')</span>
            <button onclick="toggleSidebar()" class="text-white bg-white/20 p-2 rounded-lg backdrop-blur-sm shadow-sm">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 relative scroll-smooth" id="mainContent">
            @yield('content')
        </div>

    </main>

    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden md:hidden"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const menu = document.getElementById('userMenu');
            const button = document.querySelector('button[onclick="toggleUserMenu()"]');

            if (menu && !menu.classList.contains('hidden')) {
                if (!menu.contains(e.target) && !button.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            }
        });
    </script>
</body>

</html>