<nav class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-50 font-sans">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ route('kiosk.index') }}" class="text-xl font-extrabold tracking-widest hover:text-blue-200 transition transform hover:scale-105 duration-200 font-sans">
            ÈPICERIE
        </a>

        <div class="hidden md:flex space-x-1 lg:space-x-6 text-sm font-medium">

            <a href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Dashboard
            </a>

            {{-- MENU TRANSAKSI BARU --}}
            <a href="{{ route('transaksi.index') }}"
                class="{{ request()->routeIs('transaksi*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Transaksi
            </a>
            
            <a href="{{ route('kategori.index') }}"
                class="{{ request()->routeIs('kategori*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Kategori
            </a>

            <a href="{{ route('inventaris.index') }}"
                class="{{ request()->routeIs('inventaris*') || request()->routeIs('produk*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Inventaris
            </a>

            <a href="{{ route('laporan.index') }}"
                class="{{ request()->routeIs('laporan*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Laporan
            </a>
            
            <a href="{{ route('slider.index') }}"
                class="{{ request()->routeIs('slider*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Slider
            </a>

            <a href="{{ route('kurir.index') }}"
                class="{{ request()->routeIs('kurir*') ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'text-blue-100 hover:text-white transition opacity-80 hover:opacity-100' }}">
                Pengiriman
            </a>

        </div>

        <div class="relative ml-3">
            <div>
                <button type="button" onclick="toggleDropdown()" class="flex items-center max-w-xs bg-white/10 hover:bg-white/20 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white p-1 pr-3 transition group backdrop-blur-sm border border-white/20" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                    @if(Auth::user()->foto_profil && file_exists(public_path('storage/' . Auth::user()->foto_profil)))
                    <img class="h-8 w-8 rounded-full object-cover mr-2 border border-white/50" src="{{ asset('storage/' . Auth::user()->foto_profil) }}" alt="Foto Profil">
                    @else
                    <div class="h-8 w-8 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold text-xs mr-2 shadow-sm">
                        {{ substr(strtoupper(Auth::user()->nama ?? Auth::user()->username), 0, 1) }}
                    </div>
                    @endif
                    <span class="text-white font-semibold text-xs hidden md:block group-hover:text-white transition">
                        {{ Auth::user()->username }}
                    </span>
                    <i class="fa-solid fa-chevron-down ml-2 text-[10px] text-blue-200 group-hover:text-white transition"></i>
                </button>
            </div>
            
            <div id="user-menu-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-2xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform transition-all duration-200 ease-out scale-95 opacity-0 overflow-hidden font-sans">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                    <p class="text-xs text-gray-400 mb-1 font-bold uppercase tracking-wider">Login sebagai</p>
                    <div class="flex items-center justify-between">
                        <p class="text-base font-bold text-gray-900 truncate mr-2 capitalize">{{ Auth::user()->username }}</p>
                        <span class="text-[10px] bg-blue-100 text-blue-700 border border-blue-200 px-2 py-0.5 rounded font-bold uppercase tracking-wide">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                </div>
                <div class="p-2 space-y-1">
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition group">
                        <i class="fa-regular fa-user w-5 text-gray-400 group-hover:text-blue-600 mr-2"></i> Profil Saya
                    </a>
                    <a href="{{ route('kiosk.index') }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition group">
                        <i class="fa-solid fa-store w-5 text-gray-400 group-hover:text-blue-600 mr-2"></i> Lihat Toko
                    </a>
                </div>
                <div class="p-2 pt-0 border-t border-gray-100 mt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 rounded-lg transition group mt-1">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-red-400 group-hover:text-red-600 mr-2"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('user-menu-dropdown');
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            setTimeout(() => dropdown.classList.remove('scale-95', 'opacity-0'), 10);
        } else {
            dropdown.classList.add('scale-95', 'opacity-0');
            setTimeout(() => dropdown.classList.add('hidden'), 200);
        }
    }
    window.onclick = function(e) {
        if (!document.getElementById('user-menu-button').contains(e.target)) {
            const dropdown = document.getElementById('user-menu-dropdown');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('scale-95', 'opacity-0');
                setTimeout(() => dropdown.classList.add('hidden'), 200);
            }
        }
    }
</script>