<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kurir - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .modal {
            transition: opacity 0.25s ease;
        }

        body.modal-active {
            overflow-x: hidden;
            overflow-y: hidden !important;
        }

        .slide-up-enter {
            animation: slideUp 0.3s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans pb-10">

    @include('partials.navbar')

    <div class="container mx-auto p-4 md:p-6 max-w-5xl">

        @if(session('success'))
        <div class="bg-white/90 backdrop-blur-sm border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg shadow-lg mb-6 flex items-center animate-pulse">
            <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl min-h-[600px] relative">

            <div class="flex justify-between items-center mb-6 ml-1 border-b border-gray-100 pb-4">
                <h2 class="text-blue-500 font-bold text-xl flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list"></i> Daftar Pengantaran
                </h2>
                <span class="text-xs text-white bg-blue-500 px-3 py-1 rounded-full font-bold ">Total: {{ $tugas->count() }}</span>
            </div>

            @if($tugas->isEmpty())
            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 pointer-events-none">
                <div class="bg-gray-100 p-6 rounded-full mb-4 animate-bounce">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-500 text-lg">Tugas Selesai!</h3>
                <p class="text-sm opacity-60">Belum ada paket baru untuk diantar.</p>
            </div>
            @else

            <div class="space-y-4">
                @foreach($tugas as $t)
                <div class="bg-gray-50 rounded-2xl p-5 border border-transparent hover:border-blue-100 transition duration-300 group shadow-sm hover:shadow-md flex flex-col md:flex-row gap-5 items-start md:items-center">

                    <div class="flex items-start gap-4 flex-1 w-full">
                        <div class="w-12 h-12 bg-white text-blue-500 rounded-xl flex items-center justify-center shadow-sm border border-gray-100 shrink-0">
                            <i class="fa-solid fa-box text-lg"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-800 text-sm">{{ $t->kode_transaksi }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $t->status == 'Dikirim' ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-yellow-100 text-yellow-600 border-yellow-200' }}">
                                    {{ $t->status }}
                                </span>
                            </div>

                            @php
                            $alamatTampil = 'Alamat tidak ditemukan.';
                            $googleMapsUrl = '#';
                            $idAlamat = $t->id_alamat ?? null;

                            // Definisi Koordinat Toko (WAJIB ADA DISINI)
                            $latToko = "-7.73326";
                            $longToko = "110.33121";

                            if($idAlamat) {
                            $alamatDb = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')->where('id_alamat', $idAlamat)->first();
                            } else {
                            $alamatDb = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')->where('id_user', $t->id_user_pembeli)->orderBy('created_at', 'desc')->first();
                            }

                            if ($alamatDb) {
                            $alamatTampil = $alamatDb->detail_alamat . ' (' . $alamatDb->label . ')';
                            $dest = !empty($alamatDb->plus_code) ? urlencode($alamatDb->plus_code . ' Sleman') : urlencode($alamatDb->detail_alamat . ' Sleman');

                            // String concatenation yang aman
                            $googleMapsUrl = "https://www.google.com/maps/dir/?api=1&origin=" . $latToko . "," . $longToko . "&destination=" . $dest . "&travelmode=driving";
                            }
                            @endphp

                            <p class="text-xs text-gray-500 font-bold mb-0.5">{{ $t->user->nama ?? 'Pelanggan' }}</p>
                            <p class="text-[11px] text-gray-400 leading-snug line-clamp-2 md:line-clamp-1 mb-2">{{ $alamatTampil }}</p>

                            <a href="{{ $googleMapsUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-500 hover:text-blue-700 transition group-hover:underline">
                                <i class="fa-solid fa-map-location-dot"></i> Buka Rute Maps
                            </a>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto mt-2 md:mt-0 shrink-0">

                        <button onclick="openModal('{{ $t->id_transaksi }}')" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-500 font-bold text-xs hover:bg-gray-50 hover:text-blue-600 transition shadow-sm flex items-center justify-center gap-2">
                            <i class="fa-regular fa-eye"></i> Rincian
                        </button>

                        @if($t->status == 'Dikemas' || $t->status == 'diproses')
                        <form action="{{ route('kurir.mulai', $t->id_transaksi) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-500 text-white font-bold text-xs rounded-xl hover:bg-blue-600 transition  flex items-center justify-center gap-2">
                                <i class="fa-solid fa-play"></i> Mulai
                            </button>
                        </form>

                        @elseif($t->status == 'Dikirim')
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button onclick="startTracking('{{ $t->id_transaksi }}', this)" id="btn-lacak-{{ $t->id_transaksi }}" class="flex-1 sm:flex-none px-4 py-2.5 bg-green-50 text-green-600 border border-green-200 font-bold text-xs rounded-xl hover:bg-green-100 transition">
                                <i class="fa-solid fa-satellite-dish"></i> GPS
                            </button>

                            <form action="{{ route('kurir.selesai', $t->id_transaksi) }}" method="POST" class="flex-1 sm:flex-none">
                                @csrf
                                <button type="submit" onclick="return confirm('Yakin pesanan selesai?')" class="w-full px-5 py-2.5 bg-green-500 text-white font-bold text-xs rounded-xl hover:bg-green-600 transition shadow-md shadow-green-200 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-check"></i> Selesai
                                </button>
                            </form>
                        </div>
                        <div id="status-gps-{{ $t->id_transaksi }}" class="hidden fixed bottom-4 right-4 bg-green-600 text-white text-xs px-4 py-2 rounded-full shadow-lg z-50 animate-bounce">
                            ● GPS Aktif
                        </div>
                        @endif
                    </div>

                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

    <div id="modal-detail" class="modal opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="modal-overlay absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="modal-container bg-white w-full md:max-w-3xl rounded-3xl shadow-2xl transform transition-transform overflow-hidden relative max-h-[90vh] flex flex-col">

            <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-white">
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-blue-500"></i> Rincian Paket
                </h3>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-red-100 hover:text-red-500 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div id="modal-content" class="p-6 overflow-y-auto bg-gray-50 flex-1 custom-scrollbar">
                <div class="flex flex-col items-center justify-center py-10 space-y-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-400 text-2xl"></i>
                    <p class="text-gray-400 text-xs font-bold">Memuat data...</p>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-white flex justify-end">
                <button onclick="closeModal()" class="px-6 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function startTracking(trxId, btnElement) {
            if (!navigator.geolocation) {
                alert("Browser tidak support GPS!");
                return;
            }
            btnElement.innerHTML = '<i class="fa-solid fa-check"></i> On';
            btnElement.classList.add('bg-green-200');
            document.getElementById('status-gps-' + trxId).classList.remove('hidden');
            navigator.geolocation.watchPosition((pos) => {
                console.log("Lok:", pos.coords.latitude);
            }, (err) => {});
        }

        function openModal(id) {
            const modal = document.getElementById('modal-detail');
            const content = document.getElementById('modal-content');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('modal-active');
            modal.querySelector('.modal-container').classList.add('slide-up-enter');
            fetch(`/kurir/transaksi/detail/${id}`).then(res => res.text()).then(html => {
                content.innerHTML = html;
            }).catch(err => {
                content.innerHTML = `<p class="text-center text-red-400 text-xs">Gagal memuat.</p>`;
            });
        }

        function closeModal() {
            const modal = document.getElementById('modal-detail');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('modal-active');
        }
    </script>

</body>

</html>