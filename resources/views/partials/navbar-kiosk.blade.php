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
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-white text-sm text-gray-700 border border-gray-200 rounded-lg pl-10 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-sm placeholder:text-gray-400" placeholder="Cari di Épicerie">
                </div>
            </form>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-1 text-gray-500 pr-3 border-r border-gray-200">
                
                @if(Auth::check() && in_array(Auth::user()->role, ['Karyawan', 'Pemilik', 'Admin']))
                <a href="{{ route('kurir.index') }}" class="relative h-10 w-10 flex items-center justify-center rounded-lg hover:bg-orange-50 hover:text-orange-600 text-gray-400 transition group mr-1" title="Dashboard Kurir">
                    <i class="fa-solid fa-motorcycle text-xl transition group-hover:scale-110"></i>
                </a>
                @endif

                <a href="{{ route('kiosk.checkout') }}" class="relative h-10 w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group" title="Keranjang">
                    <i class="fa-solid fa-cart-shopping text-xl transition"></i>
                    @if(isset($totalItemKeranjang) && $totalItemKeranjang > 0)
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm">
                        {{ $totalItemKeranjang }}
                    </span>
                    @else
                    <span id="cart-badge" class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-sm" style="display: none;">0</span>
                    @endif
                </a>
                
                <button class="relative h-10 w-10 hidden sm:flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group">
                    <i class="fa-regular fa-bell text-xl transition"></i>
                    <span class="absolute top-2 right-2.5 h-2 w-2 bg-red-600 rounded-full border border-white"></span>
                </button>
            </div>

            <div class="pl-2">
                @if(Auth::check())
                <a href="{{ route('kiosk.profile') }}" class="flex items-center gap-2 cursor-pointer h-10 px-2 rounded-lg hover:bg-gray-50 transition relative group">
                    @if(Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold border border-gray-200 shadow-sm">
                        {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                    </div>
                    @endif
                    <div class="hidden xl:block text-left leading-tight">
                        <p class="text-xs font-bold text-gray-700 truncate max-w-[100px]">{{ Auth::user()->nama }}</p>
                    </div>
                </a>
                @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 cursor-pointer h-10 px-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm font-bold text-sm ml-2">
                    Masuk
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="w-full bg-white pb-2 border-b border-gray-50/50">
        <div class="max-w-[1280px] mx-auto px-4 flex justify-end relative">
            
            @if(Auth::check())
                @php
                    // Ambil daftar alamat user
                    $alamatUser = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
                                    ->where('id_user', Auth::id())
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                    $alamatUtama = $alamatUser->first(); // Default ambil yang pertama

                    // Variabel buat JS
                    $hasAddress = $alamatUser->isEmpty() ? 'false' : 'true';
                @endphp

                <button onclick="toggleAddressDropdown()" class="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group focus:outline-none">
                    <i class="fa-solid fa-location-dot text-blue-600 mr-1.5"></i>
                    <span class="text-gray-500">Dikirim ke</span>
                    
                    <span id="current-address-label" class="font-bold text-gray-800 group-hover:text-blue-600 transition max-w-[150px] truncate">
                        {{ $alamatUtama ? $alamatUtama->label . ' (' . $alamatUtama->penerima . ')' : 'Tambah Alamat' }}
                    </span>
                    
                    <i class="fa-solid fa-chevron-down text-gray-400 ml-1.5 text-[10px] transition-transform duration-200" id="chevron-icon"></i>
                </button>

                <div id="address-dropdown" class="absolute top-full right-4 mt-1 w-72 bg-white rounded-xl shadow-2xl border border-gray-100 hidden z-50 overflow-hidden animate-fade-in-down">
                    <div class="bg-blue-50 px-4 py-3 border-b border-blue-100">
                        <p class="text-xs font-bold text-blue-800 uppercase tracking-wide">Pilih Lokasi Pengiriman</p>
                    </div>
                    
                    <div class="max-h-[300px] overflow-y-auto custom-scrollbar p-2 space-y-1">
                        @if($alamatUser->isEmpty())
                            <div class="text-center py-4 px-2">
                                <p class="text-xs text-gray-500 mb-2">Belum ada alamat tersimpan.</p>
                                <a href="{{ route('kiosk.profile') }}" class="block w-full bg-blue-600 text-white text-xs font-bold py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fa-solid fa-plus mr-1"></i> Tambah Alamat
                                </a>
                            </div>
                        @else
                            @foreach($alamatUser as $a)
                            <button onclick="selectAddress('{{ $a->label }}', '{{ $a->penerima }}')" class="w-full text-left px-3 py-2.5 hover:bg-gray-50 rounded-lg group transition flex items-start gap-3">
                                <div class="mt-0.5 text-gray-400 group-hover:text-blue-500">
                                    <i class="fa-solid fa-map-pin"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800 group-hover:text-blue-600">{{ $a->label }} <span class="font-normal text-gray-500">- {{ $a->penerima }}</span></p>
                                    <p class="text-[10px] text-gray-500 line-clamp-1 mt-0.5">{{ $a->detail_alamat }}</p>
                                </div>
                            </button>
                            @endforeach
                            
                            <div class="border-t border-gray-100 mt-2 pt-2 px-1">
                                <a href="{{ route('kiosk.profile') }}" class="flex items-center justify-center gap-2 w-full text-xs font-bold text-blue-600 py-2 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fa-solid fa-gear"></i> Kelola Alamat
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

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

<script>
    // 1. Fungsi Buka Tutup Dropdown
    function toggleAddressDropdown() {
        const dropdown = document.getElementById('address-dropdown');
        const chevron = document.getElementById('chevron-icon');
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    // 2. Fungsi Pilih Alamat
    function selectAddress(label, penerima) {
        document.getElementById('current-address-label').innerText = label + ' (' + penerima + ')';
        toggleAddressDropdown();
        localStorage.setItem('selected_address_label', label);
        localStorage.setItem('selected_address_penerima', penerima);
    }

    /* =========================================
       2. LOGIC DROPDOWN USER (Baru)
       ========================================= */
    function toggleUserDropdown() {
        const dropdown = document.getElementById('user-dropdown-menu');
        const chevron = document.getElementById('user-chevron');
        const addressDropdown = document.getElementById('address-dropdown'); // Ambil dropdown alamat

        // Tutup dropdown alamat jika sedang terbuka
        if (addressDropdown && !addressDropdown.classList.contains('hidden')) {
            addressDropdown.classList.add('hidden');
            document.getElementById('chevron-icon').style.transform = 'rotate(0deg)';
        }

        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    /* =========================================
       3. GLOBAL EVENT LISTENER
       ========================================= */
    document.addEventListener('DOMContentLoaded', function() {
        // Cek dulu dari PHP, apakah user ini PUNYA alamat di DB?
        // Kalau kosong (false), berarti ini user baru atau belum set alamat.
        // JANGAN biarkan JS mengambil "ingatan" alamat user lain (seperti Cebongan).
        
        const userHasAddress = {{ isset($hasAddress) ? $hasAddress : 'false' }};

        if (userHasAddress) {
            // Kalau user punya alamat, baru boleh cek LocalStorage buat balikin pilihan terakhir
            const savedLabel = localStorage.getItem('selected_address_label');
            const savedPenerima = localStorage.getItem('selected_address_penerima');
            
            if(savedLabel && savedPenerima) {
                const labelElem = document.getElementById('current-address-label');
                if(labelElem) labelElem.innerText = savedLabel + ' (' + savedPenerima + ')';
            }
        } else {
            // Kalau user GAPUNYA alamat di DB, paksa bersihkan LocalStorage!
            // Biar gak muncul alamat hantu dari login sebelumnya
            localStorage.removeItem('selected_address_label');
            localStorage.removeItem('selected_address_penerima');
        }
        
        // Klik di luar dropdown buat nutup
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('address-dropdown');
            const button = document.querySelector('button[onclick="toggleAddressDropdown()"]');
            
            if (dropdown && !dropdown.classList.contains('hidden') && !dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
                document.getElementById('chevron-icon').style.transform = 'rotate(0deg)';
            }
        });
    });
</script>