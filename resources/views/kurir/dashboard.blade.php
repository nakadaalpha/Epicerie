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

    @include('partials.navbar')
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
                        <div class="w-full">
                            <p class="font-bold text-gray-800 text-sm">{{ $t->user->nama ?? 'Pelanggan' }}</p>
                            
                            @php
                                $alamatTampil = 'Pelanggan belum menambahkan alamat.';
                                $textClass = 'text-red-500 font-bold';
                                $googleMapsUrl = '#'; 
                                
                                if($t->user) {
                                    $userId = $t->user->id_user; 
                                    
                                    // Mengambil alamat. Idealnya ambil dari id_alamat di transaksi jika ada.
                                    // Fallback ke alamat terakhir user.
                                    $alamatDb = null;
                                    
                                    // Cek apakah model Transaksi punya relasi/kolom id_alamat (opsional, sesuaikan dengan DB kamu)
                                    // if(isset($t->id_alamat)) { ... }
                                    
                                    // Default: ambil alamat terakhir user (sesuai kode lama)
                                    $alamatDb = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
                                                    ->where('id_user', $userId)
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();
                                    
                                    if ($alamatDb) {
                                        $alamatTampil = $alamatDb->detail_alamat . ' (' . $alamatDb->label . ')';
                                        $textClass = 'text-gray-500';
                                        
                                        // === 1. TENTUKAN TITIK AWAL (TOKO) ===
                                        $alamatToko = "CebonganLor, Tlogoadi, Mlati, Sleman"; 
                                        $origin = urlencode($alamatToko);

                                        // === 2. TENTUKAN TITIK TUJUAN ===
                                        $dest = '';
                                        
                                        if (!empty($alamatDb->plus_code)) {
                                            // FIX: Kode Plus pendek butuh konteks wilayah agar tidak nyasar ke negara lain.
                                            // Tambahkan ", Sleman" atau ", Yogyakarta" di belakang kode plus.
                                            $dest = urlencode($alamatDb->plus_code . ' Sleman, Yogyakarta');
                                        } elseif (!empty($alamatDb->latitude) && !empty($alamatDb->longitude)) {
                                            $dest = $alamatDb->latitude . ',' . $alamatDb->longitude;
                                        } else {
                                            $dest = urlencode($alamatDb->detail_alamat);
                                        }
                                        
                                        // === 3. LINK GOOGLE MAPS ===
                                        $googleMapsUrl = "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$dest}&travelmode=driving";
                                    }
                                }
                            @endphp
                            
                            <p class="text-xs {{ $textClass }} mt-1 leading-snug">
                                @if($textClass == 'text-red-500 font-bold')
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                @endif
                                {{ $alamatTampil }}
                            </p>

                            @if ($googleMapsUrl != '#')
                            <a href="{{ $googleMapsUrl }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-1 rounded hover:bg-blue-100 transition w-auto">
                                <i class="fa-solid fa-map-location-dot"></i> Rute dari Toko
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-2">
                        @if($t->status == 'Dikemas' || $t->status == 'diproses')
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
                setTimeout(() => { dropdown.classList.add('hidden'); }, 200);
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
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            id_transaksi: trxId,
                            lat: lat,
                            long: long
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'Error') {
                            console.error("SERVER ERROR:", data.message);
                        } else {
                            console.log("Terkirim ke server:", data);
                        }
                    })
                    .catch(error => console.error("Gagal kirim:", error));
                },
                (error) => { console.error("Gagal dapat lokasi: ", error.message); },
                { enableHighAccuracy: false, timeout: 15000, maximumAge: 0 }
            );
        }
    </script>

</body>
</html>