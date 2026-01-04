<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        @keyframes popIn {
            0% {
                transform: translate(-50%, -50%) scale(0.9);
                opacity: 0;
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .toast-center {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            z-index: 9999;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20 font-sans">

    @include('partials.navbar-kiosk')

    <div id="toast-container"></div>

    @if(session('success'))
    <div id="toast" class="toast-center bg-gray-900/95 text-white px-8 py-6 rounded-2xl shadow-2xl flex flex-col items-center gap-3 backdrop-blur-sm min-w-[300px]">
        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-xl shadow-lg shadow-blue-500/30">
            <i class="fa-solid fa-check"></i>
        </div>
        <h3 class="font-bold text-lg">Berhasil!</h3>
        <p class="text-gray-300 text-sm">{{ session('success') }}</p>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    </script>
    @endif

    <div class="max-w-[1100px] mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">

        <div class="w-full md:w-[300px] shrink-0">
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm sticky top-24">

                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden mb-5 flex items-center justify-center relative group border border-gray-100">
                    @if(Auth::check() && Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300 font-bold text-7xl">
                        {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                    </div>
                    @endif

                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center cursor-pointer" onclick="document.getElementById('fotoInput').click()">
                        <i class="fa-solid fa-camera text-white text-3xl drop-shadow-md"></i>
                    </div>
                </div>

                <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="foto_profil" id="fotoInput" class="hidden" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('fotoInput').click()" class="w-full border border-gray-200 bg-white text-gray-600 font-bold py-3 rounded-xl hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition text-sm mb-6 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-camera"></i> Ganti Foto
                    </button>
                </form>

                <div class="border-t border-gray-100 pt-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Menu Akun</p>
                    <nav class="space-y-1">
                        <a href="{{ route('kiosk.profile') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-600 font-bold rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-regular fa-user"></i></div> Biodata Diri
                        </a>
                        <a href="{{ route('kiosk.riwayat') }}" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-solid fa-clock-rotate-left"></i></div> Riwayat Transaksi
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-50 hover:text-red-600 font-bold rounded-xl transition text-left mt-2">
                                <div class="w-6 text-center"><i class="fa-solid fa-arrow-right-from-bracket"></i></div> Keluar
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
        </div>

        <div class="flex-1 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6 text-white relative overflow-hidden">
                    <i class="fa-solid fa-crown absolute -right-4 -bottom-4 text-9xl text-white opacity-10 rotate-12"></i>

                    <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Status Keanggotaan</p>
                                <h2 class="text-2xl font-bold tracking-tight">Halo, {{ explode(' ', Auth::user()->nama)[0] }}!</h2>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white text-blue-600 shadow-sm flex items-center gap-1.5">
                                        @if(Auth::user()->membership == 'Gold') <i class="fa-solid fa-crown text-yellow-500"></i>
                                        @elseif(Auth::user()->membership == 'Silver') <i class="fa-solid fa-medal text-gray-400"></i>
                                        @elseif(Auth::user()->membership == 'Bronze') <i class="fa-solid fa-medal text-orange-500"></i>
                                        @else <i class="fa-solid fa-user text-gray-400"></i>
                                        @endif
                                        {{ Auth::user()->membership }} Member
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 px-5 text-center border border-white/20 min-w-[120px]">
                            <p class="text-[10px] text-blue-100 uppercase tracking-wider mb-1">Total Belanja</p>
                            @php
                            $totalBelanja = Auth::user()->transaksi()->where('status', 'selesai')->sum('total_bayar');
                            $totalFrekuensi = Auth::user()->transaksi()->where('status', 'selesai')->count();
                            @endphp
                            <p class="text-lg font-bold font-mono">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    @php
                    $nextLevel = 'Maksimal';
                    $targetNominal = 0; $targetFrekuensi = 0;
                    $currentLevel = Auth::user()->membership;

                    if ($currentLevel == 'Classic') { $nextLevel = 'Bronze'; $targetNominal = 500000; $targetFrekuensi = 10; }
                    elseif ($currentLevel == 'Bronze') { $nextLevel = 'Silver'; $targetNominal = 1000000; $targetFrekuensi = 20; }
                    elseif ($currentLevel == 'Silver') { $nextLevel = 'Gold'; $targetNominal = 2000000; $targetFrekuensi = 30; }

                    $persenNominal = $nextLevel == 'Maksimal' ? 100 : min(100, ($totalBelanja / $targetNominal) * 100);
                    $persenFrekuensi = $nextLevel == 'Maksimal' ? 100 : min(100, ($totalFrekuensi / $targetFrekuensi) * 100);
                    $totalProgress = ($persenNominal + $persenFrekuensi) / 2;
                    @endphp

                    @if($currentLevel != 'Gold')
                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-sm font-bold text-gray-700">Progress ke {{ $nextLevel }}</p>
                                <p class="text-[10px] text-gray-500">Tingkatkan transaksi untuk naik level!</p>
                            </div>
                            <span class="text-xl font-bold text-blue-600">{{ number_format($totalProgress, 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-4 overflow-hidden">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $totalProgress }}%"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 text-xs"><i class="fa-solid fa-money-bill-wave"></i></div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex justify-between text-[10px] mb-1"><span class="text-gray-500">Nominal</span> <span class="font-bold">{{ number_format($persenNominal,0) }}%</span></div>
                                    <div class="w-full bg-gray-200 rounded-full h-1">
                                        <div class="bg-green-500 h-1 rounded-full" style="width: {{ $persenNominal }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 shrink-0 text-xs"><i class="fa-solid fa-bag-shopping"></i></div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex justify-between text-[10px] mb-1"><span class="text-gray-500">Frekuensi</span> <span class="font-bold">{{ number_format($persenFrekuensi,0) }}%</span></div>
                                    <div class="w-full bg-gray-200 rounded-full h-1">
                                        <div class="bg-purple-500 h-1 rounded-full" style="width: {{ $persenFrekuensi }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mb-6 bg-yellow-50 border border-yellow-100 rounded-xl p-4 flex items-center gap-3">
                        <i class="fa-solid fa-trophy text-yellow-500 text-2xl"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-800 uppercase">Level Maksimal</p>
                            <p class="text-[11px] text-gray-600">Nikmati seluruh keuntungan eksklusif Gold Member.</p>
                        </div>
                    </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Benefit Anda Saat Ini</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="border {{ $currentLevel == 'Bronze' ? 'border-orange-400 bg-orange-50' : 'border-gray-100 grayscale opacity-60' }} rounded-lg p-3">
                                <p class="font-bold text-xs text-orange-700 mb-1"><i class="fa-solid fa-award"></i> Bronze</p>
                                <p class="text-[10px] text-gray-500">Diskon Produk 5%</p>
                            </div>
                            <div class="border {{ $currentLevel == 'Silver' ? 'border-gray-400 bg-gray-50' : 'border-gray-100 grayscale opacity-60' }} rounded-lg p-3">
                                <p class="font-bold text-xs text-gray-600 mb-1"><i class="fa-solid fa-award"></i> Silver</p>
                                <p class="text-[10px] text-gray-500">Diskon Produk 10%</p>
                            </div>
                            <div class="border {{ $currentLevel == 'Gold' ? 'border-yellow-400 bg-yellow-50' : 'border-gray-100 grayscale opacity-60' }} rounded-lg p-3">
                                <p class="font-bold text-xs text-yellow-700 mb-1"><i class="fa-solid fa-crown"></i> Gold</p>
                                <p class="text-[10px] text-gray-500 font-bold text-yellow-800">10% Diskon + VIP</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm"><i class="fa-regular fa-id-card"></i></span>
                        Biodata Diri
                    </h2>
                    <button onclick="toggleEdit('biodata')" id="btn-edit-biodata" class="w-8 h-8 rounded-full hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition flex items-center justify-center">
                        <i class="fa-solid fa-pen text-sm"></i>
                    </button>
                </div>

                <div id="view-biodata" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-50 pb-4">
                        <div class="w-48 text-sm font-medium text-gray-400">Nama Lengkap</div>
                        <div class="flex-1 text-sm font-bold text-gray-800">{{ Auth::user()->nama }}</div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-50 pb-4">
                        <div class="w-48 text-sm font-medium text-gray-400">Username</div>
                        <div class="flex-1 text-sm font-bold text-gray-800">{{ Auth::user()->username }}</div>
                    </div>
                </div>

                <form id="form-biodata" action="{{ route('profile.update') }}" method="POST" class="hidden space-y-5 animate-fade-in">
                    @csrf
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-100 pb-2">
                        <div class="w-48 text-sm font-bold text-blue-600 pt-2 sm:pt-0">Nama Lengkap</div>
                        <div class="flex-1">
                            <input type="text" name="nama" value="{{ Auth::user()->nama }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-100 pb-2">
                        <div class="w-48 text-sm font-bold text-blue-600 pt-2 sm:pt-0">Username</div>
                        <div class="flex-1">
                            <input type="text" name="username" value="{{ Auth::user()->username }}" readonly class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 text-sm font-bold text-gray-500 cursor-not-allowed">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-4 pt-2">
                        <button type="button" onclick="toggleEdit('biodata')" class="px-5 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-gray-100 transition">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm relative">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-phone"></i></span>
                        Kontak
                    </h2>
                    <button onclick="toggleEdit('kontak')" id="btn-edit-kontak" class="w-8 h-8 rounded-full hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition flex items-center justify-center">
                        <i class="fa-solid fa-pen text-sm"></i>
                    </button>
                </div>

                <div id="view-kontak" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-50 pb-4">
                        <div class="w-48 text-sm font-medium text-gray-400">Email</div>
                        <div class="flex-1 text-sm font-bold text-gray-800 flex items-center">
                            {{ Auth::user()->email ?? 'Belum diatur' }}
                            @if(Auth::user()->email) <i class="fa-solid fa-circle-check text-blue-500 ml-2 text-xs" title="Terverifikasi"></i> @endif
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-50 pb-4">
                        <div class="w-48 text-sm font-medium text-gray-400">Nomor HP</div>
                        <div class="flex-1 text-sm font-bold text-gray-800">{{ Auth::user()->no_hp ?? 'Belum diatur' }}</div>
                    </div>
                </div>

                <form id="form-kontak" action="{{ route('profile.update') }}" method="POST" class="hidden space-y-5">
                    @csrf
                    <input type="hidden" name="nama" value="{{ Auth::user()->nama }}">

                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-100 pb-2">
                        <div class="w-48 text-sm font-bold text-blue-600 pt-2 sm:pt-0">Email</div>
                        <div class="flex-1">
                            <input type="email" name="email" value="{{ Auth::user()->email }}" placeholder="contoh@email.com" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-100 pb-2">
                        <div class="w-48 text-sm font-bold text-blue-600 pt-2 sm:pt-0">Nomor HP</div>
                        <div class="flex-1">
                            <input type="text" name="no_hp" value="{{ Auth::user()->no_hp }}" placeholder="08..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-4 pt-2">
                        <button type="button" onclick="toggleEdit('kontak')" class="px-5 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-gray-100 transition">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition">Simpan Kontak</button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-map-location-dot"></i></span>
                        Daftar Alamat
                    </h2>
                    <button onclick="openAddAddressModal()" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-xs hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah
                    </button>
                </div>

                <div id="form-address-new" class="hidden bg-gray-50 rounded-xl p-6 mb-6 border border-gray-200">
                    <form action="{{ route('profile.address.add') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <label class="text-xs font-bold text-blue-800 block mb-2"><i class="fa-solid fa-crosshairs mr-1"></i> Cara Menemukan Kode Plus</label>
                            <p class="text-[10px] text-blue-600 leading-relaxed">
                                Buka Google Maps > Tekan lama lokasi rumahmu > Salin kode unik (contoh: 78F6+R2) > Tempel di bawah.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Label (Rumah/Kost)</label>
                                <input type="text" name="label" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 mb-1 block">No HP Penerima</label>
                                <input type="text" name="no_hp_penerima" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Nama Penerima</label>
                            <input type="text" name="penerima" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Kode Plus (Dari Google Maps)</label>
                            <input type="text" name="plus_code" placeholder="Contoh: 78F6+R2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Detail alamat (Patokan)</label>
                            <textarea name="detail_alamat" rows="2" placeholder="Pagar Hitam, Depan Masjid" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="closeAddAddressModal()" class="text-gray-500 font-bold text-xs px-3 hover:text-gray-700">Batal</button>
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-xs hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">Simpan Alamat</button>
                        </div>
                    </form>
                </div>

                <div id="form-address-edit" class="hidden bg-yellow-50 rounded-xl p-6 mb-6 border border-yellow-200">
                    <form id="editAddressForm" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-bold text-yellow-800"><i class="fa-solid fa-pen-to-square mr-1"></i> Edit Alamat</h3>
                            <button type="button" onclick="closeEditAddressModal()" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Label (Rumah/Kost)</label>
                                <input type="text" id="edit_label" name="label" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 outline-none bg-white">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 mb-1 block">No HP Penerima</label>
                                <input type="text" id="edit_hp" name="no_hp_penerima" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 outline-none bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Nama Penerima</label>
                            <input type="text" id="edit_penerima" name="penerima" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Kode Plus (Dari Google Maps)</label>
                            <input type="text" id="edit_plus" name="plus_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 outline-none uppercase bg-white">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 mb-1 block">Detail alamat (Patokan)</label>
                            <textarea id="edit_detail" name="detail_alamat" rows="2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 outline-none bg-white"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="submit" class="bg-yellow-500 text-white font-bold py-2 px-4 rounded-lg text-xs hover:bg-yellow-600 transition shadow-lg shadow-yellow-500/20">Update Alamat</button>
                        </div>
                    </form>
                </div>

                @if($alamat->isEmpty())
                <div class="text-center py-10 border border-dashed border-gray-200 rounded-xl">
                    <p class="text-gray-400 text-sm">Belum ada alamat tersimpan.</p>
                </div>
                @else
                <div class="grid gap-4">
                    @foreach($alamat as $a)
                    <div class="border border-gray-100 rounded-xl p-5 hover:border-blue-400 transition bg-white relative group">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-blue-100 text-blue-600 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">{{ $a->label }}</span>
                            <span class="text-sm font-bold text-gray-800">{{ $a->penerima }}</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed max-w-[90%]">{{ $a->detail_alamat }}</p>

                        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs font-bold text-gray-400">
                            <span><i class="fa-solid fa-phone mr-1"></i> {{ $a->no_hp_penerima }}</span>
                            @if(!empty($a->plus_code))
                            <span class="text-purple-600 bg-purple-50 px-2 py-0.5 rounded flex items-center gap-1">
                                <i class="fa-solid fa-crosshairs"></i> {{ $a->plus_code }}
                            </span>
                            @endif
                        </div>

                        <div class="absolute top-5 right-5 flex gap-2">
                            <button type="button"
                                onclick="openEditAddressModal('{{ $a->id_alamat }}', '{{ $a->label }}', '{{ $a->penerima }}', '{{ $a->no_hp_penerima }}', '{{ $a->plus_code ?? '' }}', '{{ $a->detail_alamat }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-full text-gray-300 hover:text-blue-600 hover:bg-blue-50 transition">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>

                            <a href="{{ route('profile.address.delete', $a->id_alamat) }}" onclick="return confirm('Hapus alamat ini?')" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-300 hover:text-red-500 hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        function toggleEdit(section) {
            const view = document.getElementById('view-' + section);
            const form = document.getElementById('form-' + section);
            const btn = document.getElementById('btn-edit-' + section);

            if (form.classList.contains('hidden')) {
                view.classList.add('hidden');
                form.classList.remove('hidden');
                btn.classList.add('hidden');
            } else {
                view.classList.remove('hidden');
                form.classList.add('hidden');
                btn.classList.remove('hidden');
            }
        }

        // --- SCRIPT MODAL TAMBAH ALAMAT ---
        function openAddAddressModal() {
            document.getElementById('form-address-new').classList.remove('hidden');
            document.getElementById('form-address-edit').classList.add('hidden'); // Tutup edit kalau ada
        }

        function closeAddAddressModal() {
            document.getElementById('form-address-new').classList.add('hidden');
        }

        // --- SCRIPT MODAL EDIT ALAMAT ---
        function openEditAddressModal(id, label, penerima, hp, plus, detail) {
            closeAddAddressModal();
            document.getElementById('edit_label').value = label;
            document.getElementById('edit_penerima').value = penerima;
            document.getElementById('edit_hp').value = hp;
            document.getElementById('edit_plus').value = plus;
            document.getElementById('edit_detail').value = detail;

            let url = "{{ route('profile.address.update', ':id') }}";
            url = url.replace(':id', id);
            document.getElementById('editAddressForm').action = url;

            document.getElementById('form-address-edit').classList.remove('hidden');
            document.getElementById('form-address-edit').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function closeEditAddressModal() {
            document.getElementById('form-address-edit').classList.add('hidden');
        }
    </script>
</body>

</html>