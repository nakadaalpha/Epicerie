<nav class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ route('kiosk.index') }}" class="text-xl font-bold tracking-widest hover:text-blue-200 transition transform hover:scale-105 duration-200">
            ÈPICERIE
        </a>

        <div class="flex space-x-6 text-sm font-medium">

            <a href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Dashboard
            </a>

            <a href="{{ route('slider.index') }}"
                class="{{ request()->routeIs('slider*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Slider
            </a>

            <a href="{{ route('kategori.index') }}"
                class="{{ request()->routeIs('kategori*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Kategori
            </a>

            <a href="{{ route('inventaris.index') }}"
                class="{{ request()->routeIs('inventaris*') || request()->routeIs('produk*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Inventaris
            </a>

            <a href="{{ route('karyawan.index') }}"
                class="{{ request()->routeIs('karyawan*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Karyawan
            </a>

            <a href="{{ route('laporan.index') }}"
                class="{{ request()->routeIs('laporan*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Laporan
            </a>

            <a href="{{ route('kurir.index') }}"
                class="{{ request()->routeIs('kurir*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Kirim
            </a>

        </div>

        <div class="relative ml-3">

            <div>
                <button type="button"
                    onclick="toggleDropdown()"
                    class="flex items-center max-w-xs bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-0.5 pr-2 transition group"
                    id="user-menu-button"
                    aria-expanded="false"
                    aria-haspopup="true">

                    @if(Auth::user()->foto_profil && file_exists(public_path('storage/' . Auth::user()->foto_profil)))

                    <img class="h-9 w-9 rounded-full object-cover mr-2 border border-gray-200"
                        src="{{ asset('storage/' . Auth::user()->foto_profil) }}"
                        alt="Foto Profil">

                    @else

                    <div class="h-9 w-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm mr-2">
                        {{ substr(strtoupper(Auth::user()->nama ?? Auth::user()->username), 0, 1) }}
                    </div>

                    @endif
                    <span class="text-gray-700 font-semibold text-sm hidden md:block group-hover:text-blue-600 transition">
                        {{ Auth::user()->username }}
                    </span>

                    <i class="fa-solid fa-chevron-down ml-2 text-[10px] text-gray-400 group-hover:text-blue-600 transition"></i>
                </button>
            </div>

            <div id="user-menu-dropdown"
                class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-2xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform transition-all duration-200 ease-out scale-95 opacity-0 overflow-hidden font-sans">

                <div class="px-5 py-4 border-b border-gray-100 bg-white">
                    <p class="text-xs text-gray-400 mb-1 font-medium">Login sebagai</p>
                    <div class="flex items-center justify-between">
                        <p class="text-base font-bold text-gray-900 truncate mr-2 capitalize">{{ Auth::user()->username }}</p>
                        <span class="text-[10px] bg-blue-50 text-blue-600 border border-blue-100 px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                </div>

                <div class="p-2 space-y-1">
                    <a href="#" class="flex items-center px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded-xl transition group">
                        <div class="w-6 flex justify-center mr-2">
                            <i class="fa-regular fa-user text-gray-400 group-hover:text-blue-600 transition text-base"></i>
                        </div>
                        Profil Saya
                    </a>

                    <a href="{{ route('kiosk.index') }}" class="flex items-center px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded-xl transition group">
                        <div class="w-6 flex justify-center mr-2"><i class="fa-solid fa-store text-gray-400 group-hover:text-blue-600 transition text-base"></i></div>
                        Lihat Toko
                    </a>
                </div>

                <div class="p-2 pt-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition group">
                            <div class="w-6 flex justify-center mr-2">
                                <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-base group-hover:translate-x-1 transition-transform"></i>
                            </div>
                            Keluar
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
        const isHidden = dropdown.classList.contains('hidden');

        if (isHidden) {
            dropdown.classList.remove('hidden');
            setTimeout(() => {
                dropdown.classList.remove('scale-95', 'opacity-0');
                dropdown.classList.add('scale-100', 'opacity-100');
            }, 10);
        } else {
            dropdown.classList.remove('scale-100', 'opacity-100');
            dropdown.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }
    }

    window.onclick = function(event) {
        const button = document.getElementById('user-menu-button');
        const dropdown = document.getElementById('user-menu-dropdown');

        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('scale-100', 'opacity-100');
                dropdown.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        }
    }
</script>