@extends('layouts.customer')

@section('title', 'Biodata Diri')

@section('content')
<div class="space-y-6">

    {{-- 1. CARD MEMBERSHIP --}}
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

    {{-- 2. CARD BIODATA --}}
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

    {{-- 3. CARD KONTAK --}}
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
                <div class="flex-1 flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-800">{{ Auth::user()->email ?? 'Belum diatur' }}</span>
                    @if(Auth::user()->email)
                    @if(Auth::user()->hasVerifiedEmail())
                    <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-200 flex items-center gap-1">
                        <i class="fa-solid fa-check-circle"></i> Terverifikasi
                    </span>
                    @else
                    <form action="{{ route('verifikasi.manual') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[10px] font-bold text-yellow-700 bg-yellow-50 px-3 py-1.5 rounded border border-yellow-200 hover:bg-yellow-100 transition">
                            Verifikasi
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center border-b border-gray-50 pb-4">
                <div class="w-48 text-sm font-medium text-gray-400">Nomor HP</div>
                <div class="flex-1 flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-800">{{ Auth::user()->no_hp ?? 'Belum diatur' }}</span>
                    @if(Auth::user()->no_hp)
                    @if(Auth::user()->no_hp_verified_at)
                    <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-200 flex items-center gap-1">
                        <i class="fa-solid fa-check-circle"></i> Terverifikasi
                    </span>
                    @else
                    <button type="button" onclick="startOtpProcess()" class="text-[10px] font-bold text-blue-700 bg-blue-50 px-3 py-1.5 rounded border border-blue-200 hover:bg-blue-100 transition">
                        Kirim OTP
                    </button>
                    @endif
                    @endif
                </div>
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

    {{-- 4. CARD ALAMAT --}}
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

{{-- MODAL KARTU MEMBER (SINKRON DENGAN BACKGROUND ADMIN) --}}
<div id="cardModalDisplay" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-4 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative transform scale-100 transition-transform duration-300 overflow-hidden">

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
                {{-- Jika admin upload di public/images/card_bg.png --}}
                <img src="{{ asset('images/card_bg.png') }}"
                    class="absolute inset-0 w-full h-full object-cover z-0"
                    onerror="this.style.display='none'; document.getElementById('fallback-confetti').style.display='block';">

                {{-- LAYER 2: FALLBACK CONFETTI (Hanya muncul jika gambar admin tidak ada) --}}
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
                            <div class="w-[45px] h-[45px] flex items-center justify-center">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(45)
                                ->margin(0)
                                ->color(255, 255, 255)
                                ->backgroundColor(0, 0, 0, 0) // Membuat background QR transparan
                                ->generate(Auth::user()->id_user) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL OTP --}}
<div id="otpModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative transform scale-100 transition-transform duration-300">
        <button onclick="closeOtpModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm"><i class="fa-solid fa-shield-halved"></i></div>
            <h3 class="font-bold text-xl text-gray-800">Verifikasi No. HP</h3>
            <p class="text-xs text-gray-500 mt-1">Masukkan kode OTP 6 digit.</p>
        </div>
        <div class="space-y-5">
            <input type="text" id="otpInput" maxlength="6" class="w-full text-center text-3xl font-bold tracking-[0.5em] border-b-2 border-gray-300 focus:border-blue-600 focus:outline-none py-2 bg-transparent transition-colors placeholder:text-gray-300 placeholder:tracking-normal placeholder:text-sm" placeholder="KODE">
            <button onclick="submitOtp()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition shadow-lg shadow-blue-200">Verifikasi Sekarang</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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

    function openAddAddressModal() {
        document.getElementById('form-address-new').classList.remove('hidden');
        document.getElementById('form-address-edit').classList.add('hidden');
    }

    function closeAddAddressModal() {
        document.getElementById('form-address-new').classList.add('hidden');
    }

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

    // --- OTP LOGIC ---
    function startOtpProcess() {
        fetch("{{ route('phone.requestOtp') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            })
            .then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    alert("SIMULASI OTP: " + data.debug_otp);
                    document.getElementById('otpModal').classList.remove('hidden');
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert("Terjadi kesalahan sistem."));
    }

    function submitOtp() {
        const code = document.getElementById('otpInput').value;
        fetch("{{ route('phone.verifyOtp') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    otp: code
                })
            })
            .then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    alert("Berhasil Verifikasi!");
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }

    function closeOtpModal() {
        document.getElementById('otpModal').classList.add('hidden');
    }

    // --- MODAL KARTU ---
    function openCardModal() {
        const modal = document.getElementById('cardModalDisplay');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.children[0].classList.remove('scale-95', 'opacity-0');
            modal.children[0].classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCardModal() {
        const modal = document.getElementById('cardModalDisplay');
        modal.children[0].classList.remove('scale-100', 'opacity-100');
        modal.children[0].classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }
    document.getElementById('cardModalDisplay').addEventListener('click', function(e) {
        if (e.target === this) closeCardModal();
    });
</script>
@endpush