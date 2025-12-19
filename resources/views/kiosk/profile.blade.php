<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-gray-700">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1000px] mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">

        <div class="w-full md:w-[300px] shrink-0">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm sticky top-24">

                <div class="aspect-square bg-gray-100 rounded-md overflow-hidden mb-4 flex items-center justify-center relative group border border-gray-100">
                    @if(Auth::check() && Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-user text-9xl"></i>
                    </div>
                    @endif
                </div>

                <button class="w-full border border-gray-300 bg-white text-gray-700 font-bold py-2.5 rounded-lg hover:border-blue-600 hover:text-blue-600 transition text-sm">
                    Pilih Foto
                </button>

                <p class="text-xs text-gray-500 mt-4 leading-relaxed">
                    Besar file: maksimum 10.000.000 bytes (10 Megabytes). Ekstensi file yang diperbolehkan: .JPG .JPEG .PNG
                </p>
            </div>
        </div>

        <div class="flex-1">

            <div class="mb-10">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Biodata Diri</h2>

                <div class="flex flex-col sm:flex-row sm:items-center py-3">
                    <div class="w-40 text-sm text-gray-500">Nama</div>
                    <div class="flex-1 text-sm text-gray-800 font-bold flex items-center gap-2">
                        {{ Auth::user()->nama ?? 'Alpha Nakada' }}
                        <a href="#" class="text-blue-600 font-bold text-xs ml-2 hover:underline hover:text-blue-800">Ubah</a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center py-3">
                    <div class="w-40 text-sm text-gray-500">Tanggal Lahir</div>
                    <div class="flex-1 text-sm text-gray-800 font-bold flex items-center gap-2">
                        26 Desember 2002
                        <a href="#" class="text-blue-600 font-bold text-xs ml-2 hover:underline hover:text-blue-800">Tanggal Lahir</a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center py-3">
                    <div class="w-40 text-sm text-gray-500">Jenis Kelamin</div>
                    <div class="flex-1 text-sm text-gray-800 font-bold flex items-center gap-2">
                        Pria
                        <a href="#" class="text-blue-600 font-bold text-xs ml-2 hover:underline hover:text-blue-800">Ubah</a>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Kontak</h2>

                <div class="flex flex-col sm:flex-row sm:items-center py-3">
                    <div class="w-40 text-sm text-gray-500">Email</div>
                    <div class="flex-1 text-sm text-gray-800 font-bold flex items-center gap-2 flex-wrap">
                        <span>{{ Auth::user()->email ?? 'caxeace@gmail.com' }}</span>
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 rounded">Terverifikasi</span>
                        <a href="#" class="text-blue-600 font-bold text-xs ml-2 hover:underline hover:text-blue-800">Ubah</a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center py-3">
                    <div class="w-40 text-sm text-gray-500">Nomor HP</div>
                    <div class="flex-1 text-sm text-gray-800 font-bold flex items-center gap-2 flex-wrap">
                        <span>{{ Auth::user()->no_hp ?? '6285158941664' }}</span>
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 rounded">Terverifikasi</span>
                        <a href="#" class="text-blue-600 font-bold text-xs ml-2 hover:underline hover:text-blue-800">Ubah</a>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                    <h2 class="text-lg font-bold text-gray-800">Daftar Alamat</h2>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" placeholder="Tulis Nama Alamat / Kota / Kecamatan..." class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-sm transition">
                    </div>
                    <button class="w-full sm:w-auto shrink-0 bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg text-sm hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Alamat Baru
                    </button>
                </div>

                <div class="border-2 border-blue-200 bg-blue-50/50 rounded-xl p-6 mb-4 relative transition hover:border-blue-300">
                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-blue-600 rounded-r"></div>

                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pl-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-gray-500 text-xs font-extrabold uppercase tracking-wide">Rumah</span>
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">Utama</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-base mb-1">{{ Auth::user()->nama ?? 'Alpha Nakada Mulyani' }}</h3>
                            <p class="text-sm text-gray-800 font-bold mb-1">{{ Auth::user()->no_hp ?? '6285158941664' }}</p>
                            <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                Jl. Ki Ageng Gringsing, Kec. Klaten Utara, Kabupaten Klaten, Jawa Tengah, 57434 <br>
                                <span class="text-gray-400 text-xs">(Rumah pagar hitam dekat warung)</span>
                            </p>

                            <div class="flex items-center gap-2 text-blue-600 text-xs font-bold mb-5 bg-blue-100 w-fit px-2 py-1 rounded">
                                <i class="fa-solid fa-location-dot"></i> Sudah Pinpoint
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm font-bold text-blue-600 items-center">
                                <button class="hover:text-blue-800 transition">Share</button>
                                <span class="text-gray-300">|</span>
                                <button class="hover:text-blue-800 transition">Ubah Alamat</button>
                            </div>
                        </div>

                        <div class="text-blue-600 text-2xl hidden sm:block">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl p-6 mb-4 relative transition hover:border-gray-300 hover:shadow-sm bg-white">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-gray-500 text-xs font-extrabold uppercase tracking-wide">Kos</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-base mb-1">{{ Auth::user()->nama ?? 'Alpha Nakada' }}</h3>
                            <p class="text-sm text-gray-800 font-bold mb-1">{{ Auth::user()->no_hp ?? '6285158941664' }}</p>
                            <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                Wisma Garuda Panjen, Gg. Panji 2, Wedomartani <br>
                                <span class="text-gray-400 text-xs">(Kos Garuda Panjen tipe A)</span>
                            </p>

                            <div class="flex items-center gap-2 text-green-600 text-xs font-bold mb-5 w-fit px-0 py-1 rounded">
                                <i class="fa-solid fa-location-dot"></i> Sudah Pinpoint
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm font-bold text-blue-600 items-center">
                                <button class="hover:text-blue-800 transition">Share</button>
                                <span class="text-gray-300">|</span>
                                <button class="hover:text-blue-800 transition">Ubah Alamat</button>
                                <span class="text-gray-300">|</span>
                                <button class="hover:text-blue-800 transition">Jadikan Alamat Utama & Pilih</button>
                                <span class="text-gray-300">|</span>
                                <button class="hover:text-red-600 text-red-500 transition">Hapus</button>
                            </div>
                        </div>

                        <button class="bg-blue-600 text-white font-bold py-2 px-8 rounded-lg text-sm hover:bg-blue-700 transition shadow-sm">
                            Pilih
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

</body>

</html>