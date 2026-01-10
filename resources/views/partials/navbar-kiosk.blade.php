<nav class="bg-white fixed top-0 w-full z-50 shadow-sm border-b border-gray-100 font-sans transition-all duration-300">

    {{-- BARIS 1: LOGO, DESKTOP SEARCH, ICONS --}}
    <div class="max-w-[1280px] mx-auto px-4 py-3 md:py-0 md:h-[70px] flex items-center justify-between gap-4">

        {{-- A. LOGO & DESKTOP KATEGORI --}}
        <div class="flex items-center gap-6 shrink-0">
            <a href="{{ route('kiosk.index') }}" class="text-2xl md:text-3xl font-extrabold text-blue-600 tracking-tight leading-none" style="font-family: 'Nunito', sans-serif;">
                Épicerie
            </a>

            {{-- Kategori Desktop (Hidden on Mobile) --}}
            <div class="hidden md:flex items-center h-10 group relative cursor-pointer ml-1">
                <div class="h-full flex items-center px-4 group-hover:bg-gray-50 transition gap-1 rounded-lg">
                    <span class="text-sm text-gray-600 font-semibold group-hover:text-blue-600 transition">Kategori</span>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 group-hover:text-blue-600"></i>
                </div>
                <div class="absolute top-full left-0 w-[250px] pt-2 hidden group-hover:block z-50">
                    <div class="bg-white shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] rounded-xl border border-gray-100 overflow-hidden">
                        <div class="py-2">
                            @php
                            $kategoriNav = \App\Models\Kategori::orderBy('nama_kategori', 'asc')->get();
                            @endphp
                            <div class="flex flex-col max-h-[400px] overflow-y-auto custom-scrollbar p-1">
                                @foreach($kategoriNav as $cat)
                                <a href="{{ route('kiosk.search', ['kategori[]' => $cat->id_kategori]) }}" class="mx-1 px-4 py-2.5 text-sm text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:font-bold transition flex items-center justify-between group/item">
                                    <span>{{ $cat->nama_kategori }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
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
                <button onclick="openQrModal()" class="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="QR Member Saya">
                    <i class="fa-solid fa-qrcode text-lg md:text-xl transition transform group-hover:scale-110"></i>
                </button>
                @endif

                {{-- Tombol Keranjang --}}
                <a href="{{ route('kiosk.cart') }}" class="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="Keranjang">
                    <i class="fa-solid fa-cart-shopping text-lg md:text-xl transition"></i>
                    @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm">{{ $totalItemKeranjang }}</span>
                    @else
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm" style="display: none;">0</span>
                    @endif
                </a>

                {{-- Hamburger Menu (Mobile Only) --}}
                <button onclick="toggleMobileMenu()" class="relative h-9 w-9 flex md:hidden items-center justify-center rounded-lg hover:bg-gray-50 text-gray-600 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>

            {{-- D. PROFILE DROPDOWN (Desktop Only) --}}
            <div class="pl-2 relative group hidden md:block">
                @if(Auth::check())
                <a href="{{ route('kiosk.profile') }}">
                    <div class="flex items-center gap-2 cursor-pointer h-10 px-2 rounded-lg hover:bg-gray-50 transition">
                        @if(Auth::user()->foto_profil)
                        <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
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

    {{-- BARIS 2: MOBILE SEARCH (Visible only on Mobile) --}}
    <div class="md:hidden px-4 pb-3">
        <form action="{{ route('kiosk.search') }}" method="GET" class="w-full">
            <div class="relative group w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-blue-600 transition text-sm"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-gray-50 text-sm text-gray-700 border border-gray-200 rounded-lg pl-9 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400" placeholder="Cari barang...">
            </div>
        </form>
    </div>

    {{-- BARIS 3: MOBILE MENU (Hidden by default, toggled by Hamburger) --}}
    <div id="mobile-menu-container" class="hidden md:hidden border-t border-gray-100 bg-white absolute w-full left-0 shadow-lg z-40">
        <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">

            {{-- Profile Mobile --}}
            @if(Auth::check())
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                @if(Auth::user()->foto_profil)
                <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-10 h-10 rounded-full object-cover">
                @else
                <div class="w-10 h-10 rounded-full bg-gray-800 text-white flex items-center justify-center font-bold">
                    {{ substr(Auth::user()->nama, 0, 1) }}
                </div>
                @endif
                <div>
                    <p class="font-bold text-gray-800">{{ Auth::user()->nama }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->membership }} Member</p>
                </div>
            </div>
            @endif

            {{-- Accordion Kategori Mobile --}}
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kategori</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($kategoriNav as $cat)
                    <a href="{{ route('kiosk.search', ['kategori[]' => $cat->id_kategori]) }}" class="text-sm bg-gray-50 text-gray-700 p-2 rounded-lg text-center hover:bg-blue-50 hover:text-blue-600 transition truncate">
                        {{ $cat->nama_kategori }}
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Menu User Mobile --}}
            @if(Auth::check())
            <div class="space-y-1">
                <a href="{{ route('kiosk.profile') }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg font-medium"><i class="fa-regular fa-user mr-2 text-gray-400"></i> Biodata Diri</a>
                <a href="{{ route('kiosk.riwayat') }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg font-medium"><i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i> Riwayat Transaksi</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg font-bold"><i class="fa-solid fa-arrow-right-from-bracket mr-2 text-red-400"></i> Keluar</button>
                </form>
            </div>
            @else
            <a href="{{ route('login') }}" class="block w-full bg-blue-600 text-white text-center py-3 rounded-xl font-bold">Masuk Sekarang</a>
            @endif
        </div>
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

{{-- SPACER BODY (Disesuaikan agar konten tidak tertutup navbar) --}}
{{-- Pada mobile navbar lebih tinggi karena search bar, jadi spacer perlu penyesuaian --}}
<div class="h-[145px] md:h-[105px] w-full bg-gray-50"></div>

{{-- Script Toggle Mobile Menu --}}
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu-container');
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }
</script>

{{-- ... (Sisa kode Modal Address, Modal QR, dan Script lainnya tetap sama) ... --}}
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
        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-gray-50/30">
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

{{-- --- MODAL QR MEMBER (YANG DI-GENERATE OTOMATIS) --- --}}
<div id="qrMemberModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="qrBackdrop" onclick="closeQrModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm relative z-10 transform scale-95 opacity-0 transition-all duration-300" id="qrContent">
        <button onclick="closeQrModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        <div class="p-8 flex flex-col items-center text-center">
            <div class="mb-6">
                <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">Kartu Member Digital</h3>
                <p class="text-sm text-gray-500 mt-1">Tunjukkan QR ini kepada kasir.</p>
            </div>
            <div class="p-4 bg-white border-2 border-dashed border-blue-200 rounded-2xl shadow-sm mb-4 flex flex-col items-center justify-center">
                {{-- GENERATE QR CODE VIA LIBRARY --}}
                <div class="bg-white p-2">
                    {!! QrCode::size(200)->color(37, 99, 235)->generate(Auth::user()->id_user) !!}
                </div>
            </div>
            <div class="w-full bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="font-bold text-gray-800 text-lg">{{ Auth::user()->nama }}</p>
                <div class="flex justify-center items-center gap-2 mt-1">
                    <span class="text-xs font-bold px-2 py-0.5 rounded border {{ Auth::user()->membership_color }}">
                        {{ Auth::user()->membership }} Member
                    </span>
                    <span class="text-xs text-gray-400 font-mono">ID: {{ Auth::user()->id_user }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    // --- QR MODAL LOGIC ---
    function openQrModal() {
        const modal = document.getElementById('qrMemberModal');
        const backdrop = document.getElementById('qrBackdrop');
        const content = document.getElementById('qrContent');
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

    function closeQrModal() {
        const modal = document.getElementById('qrMemberModal');
        const backdrop = document.getElementById('qrBackdrop');
        const content = document.getElementById('qrContent');
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

    // --- ADDRESS MODAL LOGIC ---
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
        localStorage.setItem('selected_address_label', label);
        localStorage.setItem('selected_address_penerima', penerima);
        window.highlightCurrentAddress();
        setTimeout(() => window.closeAddressModal(), 300);
    }

    window.highlightCurrentAddress = function() {
        const savedLabel = localStorage.getItem('selected_address_label');
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
        const savedLabel = localStorage.getItem('selected_address_label');
        const savedPenerima = localStorage.getItem('selected_address_penerima');
        if (savedLabel && savedPenerima) {
            const el = document.getElementById('current-address-label');
            if (el) el.innerText = savedLabel + ' (' + savedPenerima + ')';
        }
    });
</script>
@endif