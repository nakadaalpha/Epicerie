<nav class="bg-white fixed top-0 w-full z-50 shadow-sm border-b border-gray-100 font-sans">

    <div class="max-w-[1280px] mx-auto px-4 h-[70px] flex items-center justify-between">

        <div class="flex items-center gap-6 shrink-0">
            <a href="{{ route('kiosk.index') }}" class="text-3xl font-extrabold text-blue-600 tracking-tight leading-none" style="font-family: 'Nunito', sans-serif;">
                Épicerie
            </a>

            <div class="hidden md:flex items-center h-10 group relative cursor-pointer ml-1">

                <div class="h-full flex items-center px-4 group-hover:bg-gray-50 transition gap-1 rounded-lg">
                    <span class="text-sm text-gray-600 font-semibold group-hover:text-blue-600 transition">Kategori</span>
                </div>

                <div class="absolute top-full left-0 w-[250px] pt-2 hidden group-hover:block z-50 hover:block">

                    <div class="bg-white shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] rounded-xl border border-gray-100 overflow-hidden">

                        <div class="py-2">
                            @php
                            $kategoriNav = \App\Models\Kategori::orderBy('nama_kategori', 'asc')->get();
                            @endphp

                            <div class="flex flex-col max-h-[400px] overflow-y-auto custom-scrollbar p-1">

                                @foreach($kategoriNav as $cat)
                                <a href="{{ route('kiosk.index', ['kategori' => $cat->id_kategori]) }}" class="mx-1 px-4 py-2.5 text-sm text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:font-bold transition flex items-center justify-between group/item">
                                    <span>{{ $cat->nama_kategori }}</span>
                                </a>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 px-1 lg:px-5 hidden md:block">
            <form action="{{ route('kiosk.index') }}" method="GET" class="w-full">
                <div class="relative group w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-blue-600 transition text-lg"></i>
                    </span>

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="w-full bg-white text-sm text-gray-700 border border-gray-200 rounded-lg pl-10 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400"
                        placeholder="Cari di Épicerie">
                </div>
            </form>
        </div>

        <div class="flex items-center gap-2 shrink-0">

            <div class="flex items-center gap-1 text-gray-500 pr-3 border-r border-gray-200">

                <a href="{{ route('kiosk.checkout') }}" class="relative h-10 w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="Keranjang">
                    <i class="fa-solid fa-cart-shopping text-xl transition"></i>
                    @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                    <span class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm">
                        {{ $totalItemKeranjang }}
                    </span>
                    @endif
                </a>

                <button class="relative h-10 w-10 hidden sm:flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group">
                    <i class="fa-regular fa-bell text-xl transition"></i>
                    <span class="absolute top-2 right-2.5 h-2 w-2 bg-red-600 rounded-full border border-white"></span>
                </button>
            </div>

            <div class="pl-2">
                <a href="{{ route('kiosk.profile') }}" class="flex items-center gap-2 cursor-pointer h-10 px-2 rounded-lg hover:bg-gray-50 transition relative group">

                    @if(Auth::check() && Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold border border-gray-200 shadow-sm">
                        {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                    </div>
                    @endif

                    <div class="hidden xl:block text-left leading-tight">
                        <p class="text-xs font-bold text-gray-700 truncate max-w-[100px]">{{ Auth::user()->nama ?? 'User' }}</p>
                    </div>

                </a>
            </div>
        </div>
    </div>

    <div class="w-full bg-white pb-2">
        <div class="max-w-[1280px] mx-auto px-4 flex justify-end">
            <div class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group">
                <i class="fa-solid fa-location-dot text-blue-600 mr-1.5"></i>
                <span class="text-gray-500">Dikirim ke</span>
                <span class="font-bold text-gray-800 group-hover:text-blue-600 transition">Rumah Alpha Nakada...</span>
                <i class="fa-solid fa-chevron-down text-gray-400 ml-1.5 text-[10px]"></i>
            </div>
        </div>
    </div>

</nav>

<div class="h-[105px] w-full bg-gray-50"></div>