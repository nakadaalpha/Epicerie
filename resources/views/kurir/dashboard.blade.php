<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kurir - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 font-sans pb-20">

    <nav class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-widest hover:text-blue-200 transition transform hover:scale-105 duration-200">
                ÈPICERIE
            </a>

            <div class="hidden md:flex space-x-6 text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">Dashboard</a>
                <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">Kategori</a>
                <a href="{{ route('inventaris.index') }}" class="{{ request()->routeIs('inventaris*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">Inventaris</a>
                <a href="{{ route('karyawan.index') }}" class="{{ request()->routeIs('karyawan*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">Karyawan</a>
                <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan*') ? 'border-b-2 border-white pb-1 font-bold' : 'opacity-80 hover:opacity-100 transition' }}">Laporan</a>
                
                <a href="{{ route('kurir.index') }}" class="border-b-2 border-white pb-1 font-bold">
                    Kirim
                </a>
            </div>

            <div class="relative ml-3">
                <div>
                    <button type="button" onclick="toggleDropdown()" class="flex items-center max-w-xs bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-0.5 pr-2 transition group" id="user-menu-button">
                        @if(Auth::user()->foto_profil && file_exists(public_path('storage/' . Auth::user()->foto_profil)))
                        <img class="h-9 w-9 rounded-full object-cover mr-2 border border-gray-200" src="{{ asset('storage/' . Auth::user()->foto_profil) }}" alt="Foto Profil">
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

                <div id="user-menu-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-2xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform transition-all duration-200 ease-out scale-95 opacity-0 overflow-hidden font-sans">
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
                            <div class="w-6 flex justify-center mr-2"><i class="fa-regular fa-user text-gray-400 group-hover:text-blue-600 transition text-base"></i></div>
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
                                <div class="w-6 flex justify-center mr-2"><i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-base group-hover:translate-x-1 transition-transform"></i></div>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="p-4 max-w-md mx-auto space-y-4 mt-4">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
            {{ session('success') }}
        </div>
        @endif

        <h2 class="font-bold text-gray-700 text-sm uppercase tracking-wide mb-2 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-blue-600"></i> Daftar Pengantaran
        </h2>

        @if($tugas->isEmpty())
        <div class="text-center py-10 bg-white rounded-xl shadow-sm border border-gray-200">
            <i class="fa-solid fa-mug-hot text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 text-sm">Belum ada tugas pengantaran.</p>
        </div>
        @else
            @foreach($tugas as $t)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
                
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                    <span class="font-bold text-gray-800 text-sm">{{ $t->kode_transaksi }}</span>
                    <span class="text-xs px-2 py-1 rounded font-bold {{ $t->status == 'Dikirim' ? 'bg-blue-100 text-blue-600' : 'bg-yellow-100 text-yellow-600' }}">
                        {{ strtoupper($t->status) }}
                    </span>
                </div>

                <div class="p-4">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $t->user->nama ?? 'Pelanggan' }}</p>
                            
                            @php
                                $alamatTampil = 'Pelanggan belum menambahkan alamat.';
                                $textClass = 'text-red-500 font-bold'; // Default merah kalau kosong
                                
                                if($t->user) {
                                    $userId = $t->user->id_user; 
                                    $alamatDb = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
                                                    ->where('id_user', $userId)
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();
                                    
                                    if ($alamatDb) {
                                        $alamatTampil = $alamatDb->detail_alamat . ' (' . $alamatDb->label . ')';
                                        $textClass = 'text-gray-500'; // Jadi abu-abu kalau ada alamatnya
                                    }
                                }
                            @endphp
                            
                            <p class="text-xs {{ $textClass }} mt-1 leading-snug">
                                @if($textClass == 'text-red-500 font-bold')
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                @endif
                                {{ $alamatTampil }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        @if($t->status == 'Dikemas')
                            <form action="{{ route('kurir.mulai', $t->id_transaksi) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-location-arrow"></i> Mulai Antar
                                </button>
                            </form>
                        
                        @elseif($t->status == 'Dikirim')
                            <button onclick="startTracking('{{ $t->id_transaksi }}', this)" id="btn-lacak-{{ $t->id_transaksi }}" class="flex-1 bg-green-100 text-green-700 border border-green-200 font-bold py-3 rounded-lg animate-pulse flex items-center justify-center gap-2">
                                <i class="fa-solid fa-satellite-dish"></i> Aktifkan GPS
                            </button>

                            <form action="{{ route('kurir.selesai', $t->id_transaksi) }}" method="POST" class="w-auto">
                                @csrf
                                <button type="submit" onclick="return confirm('Yakin pesanan sudah sampai?')" class="bg-gray-800 text-white font-bold py-3 px-4 rounded-lg hover:bg-gray-900 transition">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div id="status-gps-{{ $t->id_transaksi }}" class="hidden bg-green-500 text-white text-xs text-center py-1 absolute bottom-0 w-full font-bold">
                    GPS AKTIF - MENGIRIM LOKASI...
                </div>

            </div>
            @endforeach
        @endif

    </div>

    <script>
        // --- 1. SCRIPT NAVBAR DROPDOWN ---
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
                    setTimeout(() => { dropdown.classList.add('hidden'); }, 200);
                }
            }
        }

        // --- 2. SCRIPT GPS TRACKING ---
        let watchId = null;

        function startTracking(trxId, btnElement) {
            if (!navigator.geolocation) {
                alert("Browser HP kamu tidak support GPS!");
                return;
            }

            btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> GPS Aktif';
            btnElement.classList.remove('bg-green-100', 'text-green-700', 'animate-pulse');
            btnElement.classList.add('bg-green-600', 'text-white');
            document.getElementById('status-gps-' + trxId).classList.remove('hidden');

            alert("GPS Diaktifkan! Jangan tutup halaman ini selama perjalanan.");

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const long = position.coords.longitude;
                    console.log("Lokasi dapet:", lat, long);

                    fetch('/api/update-lokasi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            id_transaksi: trxId,
                            lat: lat,
                            long: long
                        })
                    })
                    .then(response => response.json())
                    .then(data => console.log("Terkirim ke server:", data))
                    .catch(error => console.error("Gagal kirim:", error));
                },
                (error) => { console.error("Gagal dapat lokasi: ", error.message); },
                { enableHighAccuracy: false, timeout: 15000, maximumAge: 0 }
            );
        }
    </script>

</body>
</html>