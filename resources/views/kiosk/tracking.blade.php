<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #{{ $trx->kode_transaksi }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
        body { font-family: 'Nunito', sans-serif; }
        #map { height: 500px; width: 100%; z-index: 1; }

        /* --- INI YANG BIKIN GERAKNYA HALUS (ANTI-LONCAT) --- */
        .leaflet-marker-icon {
            transition: transform 2s linear;
        }
    </style>
</head>
<body class="bg-gray-50 pb-10">

    @include('partials.navbar-kiosk')

    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-blue-600"></i> Lacak Pesanan
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kode Transaksi: <span class="font-bold text-gray-800">{{ $trx->kode_transaksi }}</span></p>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="font-bold text-gray-700 text-sm">Status: {{ strtoupper($trx->status) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative">
            <div id="map"></div>
            
            <div class="absolute bottom-4 left-4 right-4 md:right-auto bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-gray-200 z-[1000] md:min-w-[300px]">
                <h3 class="font-bold text-gray-800 mb-1"><i class="fa-solid fa-motorcycle text-blue-600 mr-2"></i> Posisi Kurir</h3>
                <p id="status-text" class="text-xs text-gray-500">Menunggu sinyal lokasi...</p>
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
                    <i class="fa-regular fa-clock"></i> Update terakhir: <span id="last-update">-</span>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Detail Paket</h3>
            <div class="space-y-3">
                @foreach($trx->detailTransaksi as $detail)
                <div class="flex items-center gap-4 border-b border-gray-50 last:border-0 pb-3 last:pb-0">
                    <div class="w-12 h-12 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200 text-gray-400">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">{{ $detail->produk->nama_produk ?? 'Produk' }}</p>
                        <p class="text-xs text-gray-500">{{ $detail->jumlah }} x Rp{{ number_format($detail->harga_produk_saat_beli) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        // --- A. KONFIGURASI DATA AWAL ---
        // Default koordinat (Ganti sesuai titik start Jogja tadi)
        let lat = {{ $trx->kurir_lat ?? -7.733260207743608 }}; 
        let long = {{ $trx->kurir_long ?? 110.33121377926132 }};
        const trxId = "{{ $trx->id_transaksi }}";

        // --- B. SETUP PETA (Leaflet) ---
        var map = L.map('map').setView([lat, long], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var motorIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/3063/3063823.png',
            iconSize: [40, 40], 
            iconAnchor: [20, 20], 
            popupAnchor: [0, -20]
        });

        var marker = L.marker([lat, long], {icon: motorIcon}).addTo(map)
            .bindPopup("<b>Kurir Épicerie</b><br>Sedang mengantar paketmu.").openPopup();

        // --- C. SETUP REALTIME (Pusher) ---
        
        // 1. NYALAIN LOGGING (PENTING BUAT DEBUG)
        Pusher.logToConsole = true; 

        var pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}'
        });

        var channel = pusher.subscribe('tracking.' + trxId);

        // 2. DENGERIN UPDATE LOKASI (Ini yang tadi HILANG)
        channel.bind('lokasi-update', function(data) {
            console.log("📍 Update LOKASI diterima:", data);
            
            var newLat = data.lat;
            var newLong = data.long;

            // Geser Marker (Animasi jalan karena CSS transition)
            marker.setLatLng([newLat, newLong]);
            
            // Geser Kamera Peta
            map.panTo([newLat, newLong]);

            // Update Teks Info
            document.getElementById('status-text').innerText = "Bergerak ke titik baru...";
            document.getElementById('status-text').className = "text-xs text-blue-600 font-bold";
            
            var now = new Date();
            document.getElementById('last-update').innerText = now.toLocaleTimeString();
        });

        // 3. DENGERIN UPDATE STATUS (Notif Selesai)
        channel.bind('status-update', function(data) {
            console.log("✅ Update STATUS diterima:", data);
            
            if(data.status === 'Selesai') {
                // Update UI Status
                const statusBadge = document.querySelector('.bg-white span.font-bold');
                if(statusBadge) {
                    statusBadge.innerText = "Status: SELESAI";
                    statusBadge.parentElement.className = "bg-green-500 px-4 py-2 rounded-xl shadow-sm flex items-center gap-3 text-white";
                }

                document.getElementById('status-text').innerText = "Pesanan telah sampai di tujuan.";
                document.getElementById('status-text').className = "text-xs text-green-600 font-bold";

                // Munculkan Modal
                const modal = document.getElementById('modal-selesai');
                modal.classList.remove('hidden'); 
            }
        });
    </script>

    <div id="modal-selesai" class="fixed inset-0 z-[9999] hidden flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    
    <div class="bg-white rounded-3xl shadow-2xl relative w-full max-w-sm overflow-hidden transform transition-all animate-bounce-short">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-green-100">
                <i class="fa-solid fa-house-chimney-check text-3xl"></i>
            </div>
            
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Pesanan Sampai!</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-8">
                Hore! Kurir sudah sampai di lokasi tujuan. Silakan cek pesananmu ya!
            </p>
            
            <button onclick="location.reload()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                Siap, Terima Kasih!
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes bounceShort {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    .animate-bounce-short {
        animation: bounceShort 0.5s ease-out;
    }
</style>

</body>
</html>