<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Saya - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        .modal {
            transition: opacity 0.25s ease;
        }

        body.modal-active {
            overflow-x: hidden;
            overflow-y: hidden !important;
        }

        /* Star Hover Effect */
        .star-rating:hover .star-icon {
            color: #fbbf24;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20 font-sans">

    @include('partials.navbar-kiosk')

    {{-- TOAST NOTIFICATION --}}
    @if(session('success'))
    <div class="fixed top-24 left-1/2 transform -translate-x-1/2 z-[999] bg-gray-900/90 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 backdrop-blur-sm animate-bounce">
        <i class="fa-solid fa-check-circle text-green-400"></i>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <div class="max-w-[1000px] mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">

        {{-- SIDEBAR --}}
        <div class="w-full md:w-[300px] shrink-0">
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm sticky top-24">
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden mb-5 flex items-center justify-center relative group border border-gray-100">
                    @if(Auth::user()->foto_profil)
                    <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300 font-bold text-7xl">{{ substr(Auth::user()->nama, 0, 1) }}</div>
                    @endif
                </div>
                <div class="text-center mb-6">
                    <h3 class="font-bold text-gray-800 text-lg">{{ Auth::user()->nama }}</h3>
                </div>
                <div class="border-t border-gray-100 pt-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Menu Akun</p>
                    <nav class="space-y-1">
                        <a href="{{ route('kiosk.profile') }}" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-regular fa-user"></i></div> Biodata Diri
                        </a>
                        <a href="{{ route('kiosk.riwayat') }}" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 font-medium rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-solid fa-clock-rotate-left"></i></div> Riwayat Transaksi
                        </a>
                        {{-- Menu Aktif --}}
                        <a href="{{ route('kiosk.ulasan') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-600 font-bold rounded-xl transition">
                            <div class="w-6 text-center"><i class="fa-solid fa-star"></i></div> Ulasan Saya
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

        {{-- MAIN CONTENT --}}
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-star text-yellow-500"></i> Ulasan Saya
            </h1>

            {{-- TABS --}}
            <div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-1 no-scrollbar">
                <a href="{{ route('kiosk.ulasan', ['tab' => 'menunggu']) }}"
                    class="{{ $tab == 'menunggu' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-6 py-3 rounded-t-xl transition whitespace-nowrap text-sm">
                    Menunggu Diulas
                </a>
                <a href="{{ route('kiosk.ulasan', ['tab' => 'selesai']) }}"
                    class="{{ $tab == 'selesai' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-6 py-3 rounded-t-xl transition whitespace-nowrap text-sm">
                    Riwayat Ulasan
                </a>
            </div>

            {{-- TAB 1: MENUNGGU DIULAS --}}
            @if($tab == 'menunggu')
            @if(count($menungguUlasan) > 0)
            <div class="space-y-4">
                @foreach($menungguUlasan as $item)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col sm:flex-row items-center p-5 gap-5">
                    {{-- Gambar --}}
                    <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center shrink-0 border border-gray-200 p-2">
                        @if($item->gambar) <img src="{{ asset('storage/' . $item->gambar) }}" class="w-full h-full object-contain">
                        @else <i class="fa-solid fa-box text-gray-300 text-2xl"></i> @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 w-full text-center sm:text-left">
                        <h4 class="font-bold text-gray-800 text-base mb-1">{{ $item->nama_produk }}</h4>
                        <p class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded w-fit mx-auto sm:mx-0">
                            <i class="fa-regular fa-clock mr-1"></i> Dibeli pada {{ date('d M Y', strtotime($item->tgl_beli)) }}
                        </p>
                    </div>

                    {{-- Tombol --}}
                    <button onclick="openReviewModal('{{ $item->id_produk }}', '{{ $item->nama_produk }}', '{{ $item->gambar ? asset('storage/' . $item->gambar) : '' }}')"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-lg shadow-blue-600/20 w-full sm:w-auto">
                        Tulis Ulasan
                    </button>
                </div>
                @endforeach
            </div>
            @else
            {{-- Empty State Menunggu --}}
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 text-green-500 shadow-sm">
                    <i class="fa-solid fa-clipboard-check text-4xl"></i>
                </div>
                <h3 class="text-lg font-extrabold text-gray-800">Semua produk sudah diulas!</h3>
                <p class="text-gray-500 mb-6 text-sm">Terima kasih telah membantu pembeli lain.</p>
                <a href="{{ route('kiosk.index') }}" class="inline-block bg-blue-600 text-white font-bold py-2.5 px-8 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 text-sm">
                    Belanja Lagi
                </a>
            </div>
            @endif

            {{-- TAB 2: RIWAYAT ULASAN --}}
            @else
            @if(count($riwayatUlasan) > 0)
            <div class="space-y-4">
                @foreach($riwayatUlasan as $ulasan)
                {{-- LAYOUT DISEMAKAN DENGAN "MENUNGGU DIULAS" --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:border-blue-200 transition flex flex-col sm:flex-row gap-5 items-start">

                    {{-- Gambar --}}
                    <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center shrink-0 border border-gray-200 p-2">
                        @if($ulasan->produk && $ulasan->produk->gambar) <img src="{{ asset('storage/' . $ulasan->produk->gambar) }}" class="w-full h-full object-contain">
                        @else <i class="fa-solid fa-box text-gray-300 text-2xl"></i> @endif
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1 w-full">
                        <div class="flex flex-col sm:flex-row justify-between items-start mb-2">
                            <h4 class="font-bold text-gray-800 text-base">{{ $ulasan->produk->nama_produk ?? 'Produk Dihapus' }}</h4>
                            <span class="text-[10px] text-gray-400 mt-1 sm:mt-0">{{ $ulasan->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- Bintang --}}
                        <div class="flex text-yellow-400 text-sm mb-3">
                            @for($i=1; $i<=5; $i++)
                                <i class="fa-{{ $i <= $ulasan->rating ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                        </div>

                        {{-- Komentar --}}
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 relative">
                            <i class="fa-solid fa-quote-left text-gray-300 absolute top-2 left-2 text-xs"></i>
                            <p class="text-sm text-gray-700 italic pl-4">"{{ $ulasan->komentar }}"</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            {{-- Empty State Riwayat --}}
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 shadow-sm">
                    <i class="fa-regular fa-comment-dots text-4xl"></i>
                </div>
                <h3 class="text-lg font-extrabold text-gray-800">Belum ada riwayat ulasan</h3>
                <p class="text-gray-500 text-sm">Riwayat ulasanmu akan muncul di sini setelah kamu menilai produk.</p>
            </div>
            @endif
            @endif

        </div>
    </div>

    {{-- MODAL REVIEW (Sama) --}}
    <div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReviewModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('review.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_produk" id="modalProductId">
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Berikan Ulasan</h3>
                        <button type="button" onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    <div class="bg-white px-6 py-6">
                        <div class="flex items-center gap-4 mb-6">
                            <img id="modalProductImg" src="" class="w-16 h-16 object-contain bg-white rounded-lg border border-gray-200 p-1">
                            <div>
                                <p class="font-bold text-gray-800 text-sm line-clamp-2" id="modalProductName">Nama Produk</p>
                                <p class="text-xs text-gray-500">Bagaimana kualitasnya?</p>
                            </div>
                        </div>
                        <div class="mb-6 flex justify-center gap-2">
                            @for($i=1; $i<=5; $i++)
                                <label class="cursor-pointer transition-transform hover:scale-110">
                                <input type="radio" name="rating" value="{{ $i }}" class="hidden" onclick="fillStars({{ $i }})" required>
                                <i id="star-icon-{{ $i }}" class="fa-solid fa-star text-4xl text-gray-300 transition-colors"></i>
                                </label>
                                @endfor
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Komentar</label>
                            <textarea name="komentar" rows="3" class="w-full text-sm border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ceritakan pengalamanmu..." required></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeReviewModal()" class="px-4 py-2 bg-white text-gray-700 font-bold rounded-lg border border-gray-300 text-sm hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 text-sm shadow-md transition">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function fillStars(rating) {
            for (let i = 1; i <= 5; i++) {
                const star = document.getElementById(`star-icon-${i}`);
                if (i <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            }
        }

        function openReviewModal(id, nama, gambar) {
            document.getElementById('modalProductId').value = id;
            document.getElementById('modalProductName').innerText = nama;
            const imgEl = document.getElementById('modalProductImg');
            if (gambar) {
                imgEl.src = gambar;
                imgEl.classList.remove('hidden');
            } else {
                imgEl.classList.add('hidden');
            }

            fillStars(0);
            document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = false);
            document.querySelector('textarea[name="komentar"]').value = '';

            document.getElementById('reviewModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
            document.body.classList.remove('modal-active');
        }
    </script>

</body>

</html>