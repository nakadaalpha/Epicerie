<nav class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-widest hover:text-blue-200 transition transform hover:scale-105 duration-200">
            ÈPICERIE
        </a>

        <div class="flex space-x-6 text-sm font-medium">

            <a href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Dashboard
            </a>

            <a href="{{ route('inventaris') }}"
                class="{{ request()->routeIs('inventaris*') || request()->routeIs('produk*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Inventaris
            </a>
            
            <a href="{{ route('karyawan.index') }}"
                class="{{ request()->routeIs('karyawan*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Karyawan
            </a>

            <a href="{{ route('transaksi.index') }}"
                class="{{ request()->routeIs('transaksi*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">
                Laporan
            </a>

        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs opacity-80 hidden md:block">Hi, {{ Auth::user()->username ?? 'User' }}</span>
            <div class="w-8 h-8 bg-gray-300 rounded-full border-2 border-white overflow-hidden">
                <svg class="w-full h-full text-gray-500 bg-gray-200" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
    </div>
</nav>