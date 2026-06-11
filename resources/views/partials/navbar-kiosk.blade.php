<nav class="bg-white fixed top-0 w-full z-50 shadow-sm border-b border-gray-100 font-sans transition-all duration-300">

    {{-- BARIS 1: LOGO, DESKTOP SEARCH, ICONS --}}
    <div class="max-w-[1280px] mx-auto px-4 py-3 md:py-0 md:h-[70px] flex items-center justify-between gap-4">

        {{-- A. LOGO & DESKTOP KATEGORI --}}
        <div class="flex items-center gap-6 shrink-0">
            <a href="{{ route('kiosk.index') }}" class="text-2xl md:text-3xl font-extrabold text-blue-600 tracking-tight leading-none" style="font-family: 'Nunito', sans-serif;">
                Épicerie
            </a>

            {{-- Kategori Desktop (Membuka Modal) --}}
            <div class="hidden md:flex items-center h-10 cursor-pointer ml-1" onclick="openKategoriModal()">
                <div class="h-full flex items-center px-4 hover:bg-gray-50 transition gap-1 rounded-lg group">
                    <i class="fa-solid fa-layer-group text-blue-600 mr-1 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm text-gray-600 font-semibold group-hover:text-blue-600 transition">Kategori</span>
                </div>
            </div>
        </div>

        {{-- B. DESKTOP SEARCH BAR (Hidden on Mobile) --}}
        <div class="flex-1 px-5 hidden md:block">
            <form action="{{ route('kiosk.search') }}" method="GET" class="w-full">
                <div class="relative group w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-blue-600 transition text-lg"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-white text-sm text-gray-700 border border-gray-200 rounded-lg pl-10 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400" placeholder="Cari di Épicerie">
                </div>
            </form>
        </div>

        {{-- C. ICON GROUP & HAMBURGER --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-1 text-gray-500 pr-1 md:pr-3 md:border-r border-gray-200">

                {{-- Tombol Kasir (Admin Only) --}}
                @if(Auth::check() && in_array(Auth::user()->role, ['Karyawan', 'Pemilik', 'Admin']))
                <a href="{{ route('transaksi.create') }}" class="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition" title="Menu Kasir">
                    <i class="fa-solid fa-cash-register text-lg md:text-xl"></i>
                </a>

                <a href="{{ route('dashboard') }}" class="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 hidden sm:flex" title="Dashboard">
                    <i class="fa-solid fa-shop text-lg md:text-xl"></i>
                </a>
                @endif

                {{-- Tombol QR Member --}}
                @if(Auth::check())
                <button onclick="openCardModal()" class="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="QR Member Saya">
                    <i class="fa-solid fa-qrcode text-lg md:text-xl transition transform group-hover:scale-110"></i>
                </button>
                @endif

                {{-- Tombol Keranjang (Desktop) --}}
                <a href="{{ route('kiosk.cart') }}" class="hidden md:flex relative h-9 w-9 md:h-10 md:w-10 items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="Keranjang">
                    <i class="fa-solid fa-cart-shopping text-lg md:text-xl transition"></i>
                    @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                    <span id="cart-badge-desktop" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm">{{ $totalItemKeranjang }}</span>
                    @else
                    <span id="cart-badge-desktop" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm" style="display: none;">0</span>
                    @endif
                </a>

                {{-- Search Icon Mobile --}}
                <button onclick="document.getElementById('mobile-search-form').classList.toggle('hidden')" class="relative h-9 w-9 flex md:hidden items-center justify-center rounded-lg hover:bg-gray-50 text-gray-600 transition">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </button>

            </div>

            {{-- D. PROFILE DROPDOWN (Desktop Only) --}}
            <div class="pl-2 relative group hidden md:block">
                @if(Auth::check())
                <a href="{{ route('kiosk.profile') }}">
                    <div class="flex items-center gap-2 cursor-pointer h-10 px-2 rounded-lg hover:bg-gray-50 transition">
                        @if(Auth::user()->foto_profil)
                        <img src="{{ (str_starts_with(Auth::user()->foto_profil ?? '', 'http') ? Auth::user()->foto_profil : asset('storage/' . Auth::user()->foto_profil)) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
                        @else
                        <div class="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold border border-gray-200 shadow-sm">
                            {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                        </div>
                        @endif
                        <div class="hidden xl:block text-left leading-tight">
                            <div class="flex items-center gap-1.5">
                                <p class="text-xs font-bold text-gray-700 truncate max-w-[100px]">{{ Auth::user()->nama }}</p>
                                @if(Auth::user()->membership != 'Classic')
                                <span class="text-[9px] px-1.5 py-0.5 rounded border {{ Auth::user()->membership_color }} font-bold uppercase tracking-wider scale-90 origin-left">
                                    {{ Auth::user()->membership }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                {{-- Dropdown Menu --}}
                <div class="hidden group-hover:block absolute top-full right-0 pt-2 w-64 z-[60]">
                    <div class="bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden">
                        <div class="p-1.5">
                            <a href="{{ route('kiosk.profile') }}" class="flex items-center w-full text-left px-3 py-2.5 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition group/item"><i class="fa-regular fa-user w-5 text-center mr-2 text-gray-400 group-hover/item:text-blue-600"></i> Biodata Diri</a>
                            <a href="{{ route('kiosk.riwayat') }}" class="flex items-center w-full text-left px-3 py-2.5 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition group/item"><i class="fa-solid fa-clock-rotate-left w-5 text-center mr-2 text-gray-400 group-hover/item:text-blue-600"></i> Riwayat Transaksi</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full text-left px-3 py-2.5 text-sm font-bold text-red-600 rounded-lg hover:bg-red-50 transition group/item"><i class="fa-solid fa-arrow-right-from-bracket w-5 text-center mr-2 text-red-400 group-hover/item:text-red-600"></i> Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 cursor-pointer h-10 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm font-bold text-sm ml-2">Masuk</a>
                @endif
            </div>
        </div>
    </div>

    {{-- BARIS 2: MOBILE SEARCH (Hidden by default, toggled) --}}
    <div id="mobile-search-form" class="hidden md:hidden px-4 pb-3">
        <form action="{{ route('kiosk.search') }}" method="GET" class="w-full">
            <div class="relative group w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-blue-600 transition text-sm"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-gray-50 text-sm text-gray-700 border border-gray-200 rounded-lg pl-9 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400" placeholder="Cari barang...">
            </div>
        </form>
    </div>

    {{-- BARIS 4: ADDRESS BAR --}}
    <div class="w-full bg-white pb-2 border-b border-gray-50/50">
        <div class="max-w-[1280px] mx-auto px-4 flex justify-end relative">
            @if(Auth::check())
            @php
            $alamatUser = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
            ->where('id_user', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            $alamatUtama = $alamatUser->first();
            @endphp

            <button type="button"
                onclick="window.openAddressModal()"
                class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group focus:outline-none w-full md:w-auto justify-between md:justify-start">
                <div class="flex items-center truncate">
                    <i class="fa-solid fa-location-dot text-blue-600 mr-1.5"></i>
                    <span class="text-gray-500 mr-1">Dikirim ke</span>
                    <span id="current-address-label" class="font-bold text-gray-800 group-hover:text-blue-600 transition max-w-[120px] md:max-w-[150px] truncate">
                        {{ $alamatUtama ? $alamatUtama->label . ' (' . $alamatUtama->penerima . ')' : 'Tambah Alamat' }}
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 ml-1.5 text-[10px] transition-transform duration-200 group-hover:rotate-180"></i>
            </button>

            @else
            <a href="{{ route('login') }}" class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group w-full md:w-auto justify-between md:justify-start">
                <div class="flex items-center">
                    <i class="fa-solid fa-location-dot text-gray-400 mr-1.5"></i>
                    <span class="text-gray-500 mr-1">Alamat Pengiriman</span>
                    <span class="font-bold text-gray-800 group-hover:text-blue-600 transition">Login untuk pilih</span>
                </div>
            </a>
            @endif
        </div>
    </div>

</nav>

{{-- SPACER BODY --}}
<div class="h-[105px] md:h-[105px] w-full bg-gray-50"></div>

{{-- BOTTOM NAVIGATION BAR (MOBILE ONLY) --}}
<div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-[60] pb-safe shadow-[0_-5px_15px_-10px_rgba(0,0,0,0.1)] pt-1">
    <div class="flex justify-around items-end h-[60px] pb-1 relative">
        <a href="{{ route('kiosk.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('kiosk.index') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }}">
            <i class="fa-solid fa-house text-[20px] mb-0.5"></i>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>
        <a href="{{ route('kiosk.search') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('kiosk.search') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }}">
            <i class="fa-solid fa-magnifying-glass text-[20px] mb-0.5"></i>
            <span class="text-[10px] font-bold">Pencarian</span>
        </a>
        
        {{-- Floating QR Button --}}
        <div class="w-full flex justify-center relative h-full">
            @if(Auth::check())
            <button onclick="openCardModal()" class="absolute -top-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 transition rounded-full flex flex-col items-center justify-center text-white shadow-lg shadow-blue-300 border-4 border-white active:scale-95">
                <i class="fa-solid fa-qrcode text-xl"></i>
            </button>
            <span class="absolute bottom-1 text-[10px] font-bold text-gray-500">Member</span>
            @else
            <a href="{{ route('login') }}" class="absolute -top-6 w-14 h-14 bg-gray-300 rounded-full flex flex-col items-center justify-center text-white shadow-md border-4 border-white active:scale-95">
                <i class="fa-solid fa-qrcode text-xl"></i>
            </a>
            <span class="absolute bottom-1 text-[10px] font-bold text-gray-500">Member</span>
            @endif
        </div>

        <a href="{{ route('kiosk.cart') }}" class="relative flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('kiosk.cart') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }}">
            <div class="relative">
                <i class="fa-solid fa-cart-shopping text-[20px] mb-0.5"></i>
                @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                <span class="absolute -top-1.5 -right-2.5 bg-red-600 text-white text-[9px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white">{{ $totalItemKeranjang }}</span>
                @endif
            </div>
            <span class="text-[10px] font-bold">Keranjang</span>
        </a>
        <a href="{{ Auth::check() ? route('kiosk.profile') : route('login') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('kiosk.profile') || request()->routeIs('kiosk.riwayat') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }}">
            <i class="fa-regular fa-user text-[20px] mb-0.5"></i>
            <span class="text-[10px] font-bold">{{ Auth::check() ? 'Profil' : 'Masuk' }}</span>
        </a>
    </div>
</div>

@if(Auth::check())
{{-- --- MODAL ALAMAT PENGIRIMAN --- --}}
<div id="modal-address-popup" class="fixed inset-0 z-[9999] hidden opacity-0 flex items-center justify-center p-4 font-sans transition-opacity duration-300 ease-in-out">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm cursor-pointer" onclick="window.closeAddressModal()"></div>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
            <h3 class="font-bold text-lg text-gray-800">Mau kirim belanjaan kemana?</h3>
            <button type="button" onclick="window.closeAddressModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-gray-50/30 pb-24 md:pb-6">
            <p class="text-sm text-gray-500 mb-2">Biar pengalaman belanjamu lebih baik, pilih alamat dulu.</p>
            @if($alamatUser->isEmpty())
            <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl">
                <i class="fa-solid fa-map-location-dot text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 font-bold">Kamu belum punya alamat</p>
                <a href="{{ route('kiosk.profile') }}" class="mt-3 inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition">
                    Tambah Alamat Baru
                </a>
            </div>
            @else
            @foreach($alamatUser as $key => $a)
            <div onclick="pilihAlamatIni('{{ $a->label }}', '{{ $a->penerima }}')" class="border-2 border-gray-200 hover:border-blue-500 bg-white p-4 rounded-xl cursor-pointer transition group relative">
                <div class="flex justify-between items-start">
                    <div class="flex-1 pr-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-gray-800">{{ $a->label }}</span>
                            @if($key == 0) <span class="bg-gray-200 text-gray-600 text-[10px] font-bold px-1.5 py-0.5 rounded">Utama</span> @endif
                        </div>
                        <p class="font-bold text-sm text-gray-700 mb-1">{{ $a->penerima }}</p>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $a->no_hp_penerima }} <br> {{ $a->detail_alamat }}</p>
                    </div>
                    <div class="shrink-0 flex items-center h-full pt-2">
                        <div class="indicator-selected hidden text-blue-600 text-2xl"><i class="fa-solid fa-circle-check"></i></div>
                        <button class="btn-select bg-blue-600 text-white font-bold px-4 py-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition transform scale-90 group-hover:scale-100 hover:bg-blue-700">Pilih</button>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            <a href="{{ route('kiosk.profile') }}" class="block text-center border border-gray-300 py-3 rounded-xl font-bold text-gray-600 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 transition mt-4">Kelola Alamat / Tambah Baru</a>
        </div>
    </div>
</div>

{{-- MODAL KARTU MEMBER (SINKRON DENGAN BACKGROUND ADMIN) --}}
<div id="cardModalDisplay" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-4 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative transform scale-95 opacity-0 transition-transform duration-300 overflow-hidden" id="cardContent">

        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-600 to-indigo-600">
            <div>
                <h3 class="font-extrabold text-white text-lg">Kartu Member Digital</h3>
            </div>
            <button onclick="closeCardModal()" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white hover:bg-white hover:text-indigo-600 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>

        {{-- Body: Tampilan Kartu --}}
        <div class="p-8 bg-gray-50 flex justify-center items-center">
            {{-- KARTU UTAMA: 342px x 216px --}}
            <div class="relative w-[342px] h-[216px] rounded-xl shadow-2xl overflow-hidden shrink-0 select-none bg-[#050505] text-white transition-transform hover:scale-[1.02] duration-500">

                {{-- LAYER 1: BACKGROUND GAMBAR DARI ADMIN --}}
                <img src="{{ asset('images/card_bg.png') }}"
                    class="absolute inset-0 w-full h-full object-cover z-0"
                    onerror="this.style.display='none'; document.getElementById('fallback-confetti').style.display='block';">

                {{-- LAYER 2: FALLBACK CONFETTI --}}
                <div id="fallback-confetti" class="hidden absolute inset-0 z-0">
                    <div class="absolute top-[40px] left-[15px] w-[22px] h-[6px] bg-[#0d9488]"></div>
                    <div class="absolute top-[35px] left-[40px] w-[22px] h-[12px] bg-[#1e293b] opacity-90"></div>
                    <div class="absolute top-[25px] left-[115px] w-[40px] h-[85px] bg-[#1e293b] opacity-80"></div>
                    <div class="absolute top-[18px] left-[138px] w-[12px] h-[12px] bg-[#7c2d12]"></div>
                    <div class="absolute top-[100px] left-[108px] w-[20px] h-[6px] bg-[#1e3a8a]"></div>
                    <div class="absolute top-[100px] left-[150px] w-[10px] h-[10px] bg-[#b45309]"></div>
                    <div class="absolute top-[110px] right-[30px] w-[30px] h-[35px] bg-[#1e293b] opacity-80"></div>
                    <div class="absolute top-[90px] right-[20px] w-[12px] h-[12px] bg-[#15803d]"></div>
                    <div class="absolute top-[135px] right-[65px] w-[12px] h-[6px] bg-[#c2410c]"></div>
                </div>

                {{-- LAYER 3: KONTEN TEKS & QR --}}
                <div class="relative z-10 w-full h-full">
                    <div class="absolute top-[10%] left-[7%]">
                        <h1 class="font-extrabold text-2xl tracking-widest uppercase text-white drop-shadow-md">ÉPICERIE</h1>
                    </div>

                    <div class="absolute top-[10%] right-[7%]">
                        <div class="border border-[#2dd4bf] bg-black/40 backdrop-blur-sm rounded px-2 py-1">
                            <p class="text-[8px] font-bold text-[#2dd4bf] tracking-widest uppercase">{{ Auth::user()->membership }} MEMBER</p>
                        </div>
                    </div>

                    <div class="absolute bottom-[10%] left-[7%]">
                        <p class="font-bold text-lg uppercase leading-tight text-white drop-shadow-md">{{ Str::limit(Auth::user()->nama, 18) }}</p>
                        <p class="text-[9px] text-[#94a3b8] font-mono tracking-widest">{{ Auth::user()->username }}</p>
                    </div>

                    <div class="absolute bottom-[10%] right-[7%]">
                        <div class="p-1 rounded shadow-lg bg-black/20 backdrop-blur-sm">
                            <div class="w-[45px] h-[45px] flex items-center justify-center bg-white rounded-sm">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(41)
                                ->margin(0)
                                ->color(0, 0, 0)
                                ->backgroundColor(255, 255, 255, 0)
                                ->generate(Auth::user()->id_user) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- --- MODAL KATEGORI --- --}}
<div id="modal-kategori-popup" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4 font-sans">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0 cursor-pointer" id="kategoriBackdrop" onclick="closeKategoriModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl relative z-10 overflow-hidden flex flex-col transform scale-95 opacity-0 transition-all duration-300" id="kategoriContent">
        
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg"><i class="fa-solid fa-shapes"></i></div>
                <div>
                    <h3 class="font-bold text-xl text-gray-800 tracking-tight">Kategori Produk</h3>
                    <p class="text-xs text-gray-500">Temukan barang incaranmu dengan cepat.</p>
                </div>
            </div>
            <button type="button" onclick="closeKategoriModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[70vh] bg-gray-50/50">
            @php
            $kategoriNavModal = \App\Models\Kategori::orderBy('nama_kategori', 'asc')->get();
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($kategoriNavModal as $cat)
                @php
                    $catName = strtolower($cat->nama_kategori);
                    $icon = 'fa-tag';
                    $color = 'text-blue-500';
                    $bgColor = 'bg-blue-50';
                    
                    // Logika Mapping Ikon Supermarket Lengkap
                    if (str_contains($catName, 'makanan ringan') || str_contains($catName, 'snack') || str_contains($catName, 'camilan')) {
                        $icon = 'fa-cookie-bite';
                        $color = 'text-orange-500';
                        $bgColor = 'bg-orange-50';
                    } elseif (str_contains($catName, 'minuman ringan') || str_contains($catName, 'minuman bersoda')) {
                        $icon = 'fa-wine-bottle';
                        $color = 'text-cyan-500';
                        $bgColor = 'bg-cyan-50';
                    } elseif (str_contains($catName, 'minuman instan') || str_contains($catName, 'kopi') || str_contains($catName, 'teh')) {
                        $icon = 'fa-mug-hot';
                        $color = 'text-amber-600';
                        $bgColor = 'bg-amber-50';
                    } elseif (str_contains($catName, 'makanan instan') || str_contains($catName, 'mie') || str_contains($catName, 'kaleng')) {
                        $icon = 'fa-utensils';
                        $color = 'text-red-500';
                        $bgColor = 'bg-red-50';
                    } elseif (str_contains($catName, 'sembako') || str_contains($catName, 'beras') || str_contains($catName, 'minyak')) {
                        $icon = 'fa-shopping-basket';
                        $color = 'text-emerald-600';
                        $bgColor = 'bg-emerald-50';
                    } elseif (str_contains($catName, 'bumbu') || str_contains($catName, 'rempah') || str_contains($catName, 'saus')) {
                        $icon = 'fa-pepper-hot';
                        $color = 'text-rose-600';
                        $bgColor = 'bg-rose-50';
                    } elseif (str_contains($catName, 'daging') || str_contains($catName, 'ayam') || str_contains($catName, 'ikan') || str_contains($catName, 'seafood')) {
                        $icon = 'fa-drumstick-bite';
                        $color = 'text-red-600';
                        $bgColor = 'bg-red-50';
                    } elseif (str_contains($catName, 'sayur') || str_contains($catName, 'buah') || str_contains($catName, 'segar')) {
                        $icon = 'fa-carrot';
                        $color = 'text-green-500';
                        $bgColor = 'bg-green-50';
                    } elseif (str_contains($catName, 'susu') || str_contains($catName, 'keju') || str_contains($catName, 'dairy')) {
                        $icon = 'fa-cheese';
                        $color = 'text-yellow-500';
                        $bgColor = 'bg-yellow-50';
                    } elseif (str_contains($catName, 'roti') || str_contains($catName, 'kue') || str_contains($catName, 'bakery')) {
                        $icon = 'fa-bread-slice';
                        $color = 'text-amber-500';
                        $bgColor = 'bg-amber-50';
                    } elseif (str_contains($catName, 'sabun') || str_contains($catName, 'shampoo') || str_contains($catName, 'perawatan') || str_contains($catName, 'tubuh')) {
                        $icon = 'fa-pump-soap';
                        $color = 'text-sky-500';
                        $bgColor = 'bg-sky-50';
                    } elseif (str_contains($catName, 'deterjen') || str_contains($catName, 'kebersihan') || str_contains($catName, 'rumah')) {
                        $icon = 'fa-spray-can';
                        $color = 'text-indigo-500';
                        $bgColor = 'bg-indigo-50';
                    } elseif (str_contains($catName, 'kesehatan') || str_contains($catName, 'obat') || str_contains($catName, 'medis')) {
                        $icon = 'fa-pills';
                        $color = 'text-teal-500';
                        $bgColor = 'bg-teal-50';
                    } elseif (str_contains($catName, 'bayi') || str_contains($catName, 'anak') || str_contains($catName, 'popok')) {
                        $icon = 'fa-baby-carriage';
                        $color = 'text-pink-500';
                        $bgColor = 'bg-pink-50';
                    } elseif (str_contains($catName, 'hewan') || str_contains($catName, 'pet')) {
                        $icon = 'fa-paw';
                        $color = 'text-stone-500';
                        $bgColor = 'bg-stone-50';
                    } elseif (str_contains($catName, 'es krim') || str_contains($catName, 'ice cream') || str_contains($catName, 'beku') || str_contains($catName, 'frozen')) {
                        $icon = 'fa-ice-cream';
                        $color = 'text-fuchsia-500';
                        $bgColor = 'bg-fuchsia-50';
                    }
                @endphp
                
                <a href="{{ route('kiosk.search', ['kategori[]' => $cat->id_kategori]) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition group flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full {{ $bgColor }} {{ $color }} flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <span class="font-bold text-sm text-gray-800 group-hover:text-blue-600 transition">{{ $cat->nama_kategori }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>

    // --- KATEGORI MODAL LOGIC ---
    window.openKategoriModal = function() {
        const modal = document.getElementById('modal-kategori-popup');
        const backdrop = document.getElementById('kategoriBackdrop');
        const content = document.getElementById('kategoriContent');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeKategoriModal = function() {
        const modal = document.getElementById('modal-kategori-popup');
        const backdrop = document.getElementById('kategoriBackdrop');
        const content = document.getElementById('kategoriContent');
        if (modal) {
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }

    // --- CARD MODAL LOGIC ---
    function openCardModal() {
        const modal = document.getElementById('cardModalDisplay');
        const content = document.getElementById('cardContent');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeCardModal() {
        const modal = document.getElementById('cardModalDisplay');
        const content = document.getElementById('cardContent');
        if (modal) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }
    
    // Allow closing by clicking backdrop
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('cardModalDisplay');
        if(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeCardModal();
            });
        }
    });

    // --- ADDRESS MODAL LOGIC ---
    const currentUserId = "{{ Auth::id() }}";

    window.openAddressModal = function() {
        const modal = document.getElementById('modal-address-popup');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
            document.body.style.overflow = 'hidden';
            window.highlightCurrentAddress();
        }
    }

    window.closeAddressModal = function() {
        const modal = document.getElementById('modal-address-popup');
        if (modal) {
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }

    window.pilihAlamatIni = function(label, penerima) {
        const el = document.getElementById('current-address-label');
        if (el) el.innerText = label + ' (' + penerima + ')';
        localStorage.setItem('selected_address_label_' + currentUserId, label);
        localStorage.setItem('selected_address_penerima_' + currentUserId, penerima);
        window.highlightCurrentAddress();
        setTimeout(() => window.closeAddressModal(), 300);
    }

    window.highlightCurrentAddress = function() {
        const savedLabel = localStorage.getItem('selected_address_label_' + currentUserId);
        const allCards = document.querySelectorAll('#modal-address-popup .border-2');
        if (allCards.length === 0) return;
        allCards.forEach(card => {
            const labelEl = card.querySelector('span.font-bold.text-gray-800');
            const checkmark = card.querySelector('.indicator-selected');
            const btn = card.querySelector('.btn-select');
            card.classList.remove('bg-blue-50', 'border-blue-500');
            card.classList.add('bg-white', 'border-gray-200');
            if (checkmark) checkmark.classList.add('hidden');
            if (btn) btn.classList.remove('hidden');
            if (labelEl && labelEl.innerText.trim() === savedLabel) {
                card.classList.remove('bg-white', 'border-gray-200');
                card.classList.add('bg-blue-50', 'border-blue-500');
                if (checkmark) checkmark.classList.remove('hidden');
                if (btn) btn.classList.add('hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedLabel = localStorage.getItem('selected_address_label_' + currentUserId);
        const savedPenerima = localStorage.getItem('selected_address_penerima_' + currentUserId);
        
        if (savedLabel && savedPenerima) {
            // Verifikasi apakah alamat ini benar-benar ada di daftar alamat user (untuk mencegah bug jika alamat sudah dihapus)
            const addressCards = document.querySelectorAll('#modal-address-popup .border-2');
            let addressExists = false;
            
            addressCards.forEach(card => {
                const labelEl = card.querySelector('span.font-bold.text-gray-800');
                if (labelEl && labelEl.innerText.trim() === savedLabel) {
                    addressExists = true;
                }
            });

            if (addressExists || addressCards.length === 0) {
                // addressCards.length === 0 bisa terjadi jika modal belum render atau DOM berbeda, 
                // tapi karena ini dirender oleh blade, length harusnya > 0 jika user punya alamat.
                if(addressExists) {
                    const el = document.getElementById('current-address-label');
                    if (el) el.innerText = savedLabel + ' (' + savedPenerima + ')';
                }
            } else {
                // Jika alamat di localStorage tidak ada di database user, hapus cache-nya
                localStorage.removeItem('selected_address_label_' + currentUserId);
                localStorage.removeItem('selected_address_penerima_' + currentUserId);
            }
        }
    });
</script>
@endif