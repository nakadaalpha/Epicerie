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
                <div class="absolute top-full left-0 w-[250px] pt-2 hidden group-hover:block z-50">
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
            <form action="{{ route('kiosk.search') }}" method="GET" class="w-full">
                <div class="relative group w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-blue-600 transition text-lg"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-white text-sm text-gray-700 border border-gray-200 rounded-lg pl-10 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400" placeholder="Cari di Épicerie">
                </div>
            </form>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-1 text-gray-500 pr-3 border-r border-gray-200">
                @if(Auth::check() && in_array(Auth::user()->role, ['Karyawan', 'Pemilik', 'Admin']))
                <a href="{{ route('dashboard') }}" class="relative h-10 w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600">
                    <i class="fa-solid fa-shop text-xl"></i>
                </a>
                @endif
                <a href="{{ route('kiosk.checkout') }}" class="relative h-10 w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="Keranjang">
                    <i class="fa-solid fa-cart-shopping text-xl transition"></i>
                    @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm">{{ $totalItemKeranjang }}</span>
                    @else
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm" style="display: none;">0</span>
                    @endif
                </a>
                <button class="relative h-10 w-10 hidden sm:flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group">
                    <i class="fa-regular fa-bell text-xl transition"></i>
                    <span class="absolute top-2 right-2.5 h-2 w-2 bg-red-600 rounded-full border border-white"></span>
                </button>
            </div>

            <div class="pl-2 relative group">
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

                <div class="hidden group-hover:block absolute top-full right-0 pt-2 w-64 z-[60]">
                    <div class="bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 relative overflow-hidden">
                            @if(Auth::user()->membership == 'Classic')
                            <div class="absolute top-0 right-0 p-2"><i class="fa-solid fa-award text-blue-600 text-4xl"></i></div>
                            @elseif(Auth::user()->membership == 'Gold')
                            <div class="absolute top-0 right-0 p-2"><i class="fa-solid fa-crown text-yellow-600 text-4xl"></i></div>
                            @elseif(Auth::user()->membership == 'Silver')
                            <div class="absolute top-0 right-0 p-2"><i class="fa-solid fa-award text-gray-500 text-4xl"></i></div>
                            @elseif(Auth::user()->membership == 'Bronze')
                            <div class="absolute top-0 right-0 p-2"><i class="fa-solid fa-award text-orange-600 text-4xl"></i></div>
                            @endif
                            <div class="flex justify-between items-start relative z-10">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-0.5">Akun Saya</p>
                                    <p class="text-sm font-bold text-gray-800 truncate max-w-[150px]">{{ Auth::user()->username }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1 mt-2 relative z-10">
                                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold">{{ Auth::user()->role }}</span>
                                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full border {{ Auth::user()->membership_color }} font-bold">{{ Auth::user()->membership }} Member</span>
                            </div>
                        </div>
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
                class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group focus:outline-none">
                <i class="fa-solid fa-location-dot text-blue-600 mr-1.5"></i>
                <span class="text-gray-500">Dikirim ke</span>

                <span id="current-address-label" class="font-bold text-gray-800 group-hover:text-blue-600 transition max-w-[150px] truncate">
                    {{ $alamatUtama ? $alamatUtama->label . ' (' . $alamatUtama->penerima . ')' : 'Tambah Alamat' }}
                </span>

                <i class="fa-solid fa-chevron-down text-gray-400 ml-1.5 text-[10px] transition-transform duration-200 group-hover:rotate-180"></i>
            </button>

            @else
            <a href="{{ route('login') }}" class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group">
                <i class="fa-solid fa-location-dot text-gray-400 mr-1.5"></i>
                <span class="text-gray-500">Alamat Pengiriman</span>
                <span class="font-bold text-gray-800 group-hover:text-blue-600 transition">Login untuk pilih</span>
            </a>
            @endif
        </div>
    </div>

</nav>

<div class="h-[105px] w-full bg-gray-50"></div>

@if(Auth::check())
<div id="modal-address-popup" class="fixed inset-0 z-[9999] hidden opacity-0 flex items-center justify-center p-4 font-sans transition-opacity duration-300 ease-in-out">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm cursor-pointer"
        onclick="window.closeAddressModal()">
    </div>

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] transform transition-all scale-100">

        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
            <h3 class="font-bold text-lg text-gray-800">Mau kirim belanjaan kemana?</h3>
            <button type="button"
                onclick="window.closeAddressModal()"
                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition">
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
            <div onclick="pilihAlamatIni('{{ $a->label }}', '{{ $a->penerima }}')"
                class="border-2 border-gray-200 hover:border-blue-500 bg-white p-4 rounded-xl cursor-pointer transition group relative">

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
                        <button class="btn-select bg-blue-600 text-white font-bold px-4 py-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition transform scale-90 group-hover:scale-100 hover:bg-blue-700">
                            Pilih
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            <a href="{{ route('kiosk.profile') }}" class="block text-center border border-gray-300 py-3 rounded-xl font-bold text-gray-600 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 transition mt-4">
                Kelola Alamat / Tambah Baru
            </a>
        </div>
    </div>
</div>

<script>
    // --- 1. OPEN MODAL (FADE IN) ---
    window.openAddressModal = function() {
        const modal = document.getElementById('modal-address-popup');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
            document.body.style.overflow = 'hidden';
            window.highlightCurrentAddress(); // Update visual aktif
        }
    }

    // --- 2. CLOSE MODAL (FADE OUT) ---
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

    // --- 3. SELECT ADDRESS ---
    window.pilihAlamatIni = function(label, penerima) {
        const el = document.getElementById('current-address-label');
        if (el) el.innerText = label + ' (' + penerima + ')';

        localStorage.setItem('selected_address_label', label);
        localStorage.setItem('selected_address_penerima', penerima);

        window.highlightCurrentAddress(); // Update visual langsung
        setTimeout(() => window.closeAddressModal(), 300); // Delay dikit biar smooth
    }

    // --- 4. HIGHLIGHT VISUAL (TEMA BIRU) ---
    window.highlightCurrentAddress = function() {
        const savedLabel = localStorage.getItem('selected_address_label');
        const allCards = document.querySelectorAll('#modal-address-popup .border-2'); // Ambil semua card di modal

        if (allCards.length === 0) return;

        allCards.forEach(card => {
            const labelEl = card.querySelector('span.font-bold.text-gray-800');
            const checkmark = card.querySelector('.indicator-selected');
            const btn = card.querySelector('.btn-select');

            // Reset Style
            card.classList.remove('bg-blue-50', 'border-blue-500');
            card.classList.add('bg-white', 'border-gray-200');
            if (checkmark) checkmark.classList.add('hidden');
            if (btn) btn.classList.remove('hidden');

            // Cek jika ini alamat aktif
            if (labelEl && labelEl.innerText.trim() === savedLabel) {
                card.classList.remove('bg-white', 'border-gray-200');
                card.classList.add('bg-blue-50', 'border-blue-500'); // Style Aktif Biru
                if (checkmark) checkmark.classList.remove('hidden');
                if (btn) btn.classList.add('hidden');
            }
        });
    }

    // Load saat refresh
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