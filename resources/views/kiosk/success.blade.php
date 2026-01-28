<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Épicerie</title>
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

        /* Custom Hover Star Effect */
        .star-rating:hover .star-icon {
            color: #fbbf24;
        }

        /* Kuning */
        .star-rating .star-icon:hover~.star-icon {
            color: #d1d5db;
        }

        /* Abu-abu utk bintang setelahnya */
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1100px] mx-auto px-4 py-8">

        {{-- Notifikasi --}}
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-6 text-sm font-bold shadow-sm">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-6 text-sm font-bold shadow-sm">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
        </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <nav class="flex text-sm text-gray-500 mb-2">
                    <a href="{{ route('kiosk.index') }}" class="hover:text-blue-600">Beranda</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('kiosk.riwayat') }}" class="hover:text-blue-600">Riwayat</a>
                    <span class="mx-2">/</span>
                    <span class="text-blue-600 font-bold">{{ $transaksi->kode_transaksi }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800">Detail Transaksi</h1>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Tanggal Pesanan</p>
                <p class="font-bold text-gray-700">{{ date('d F Y, H:i', strtotime($transaksi->created_at)) }} WIB</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Kolom Kiri --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Status --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Status Pesanan</p>
                        @php
                        $status = $transaksi->status ?? 'Dikemas';
                        $colorClass = 'text-yellow-600 bg-yellow-50 border-yellow-200';
                        $icon = 'fa-box-open';

                        if(strtolower($status) == 'dikirim') { $colorClass = 'text-blue-600 bg-blue-50 border-blue-200'; $icon = 'fa-truck-fast'; }
                        if(strtolower($status) == 'selesai') { $colorClass = 'text-green-600 bg-green-50 border-green-200'; $icon = 'fa-circle-check'; }
                        if(!$transaksi->id_alamat && strtolower($status) != 'selesai') {
                        $icon = 'fa-store'; $colorClass = 'text-orange-600 bg-orange-50 border-orange-200';
                        $status = 'Siap Diambil';
                        }
                        @endphp
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-extrabold text-gray-800">{{ strtoupper($status) }}</h2>
                        </div>
                    </div>
                    <div class="h-12 w-12 rounded-full flex items-center justify-center border {{ $colorClass }}">
                        <i class="fa-solid {{ $icon }} text-xl"></i>
                    </div>
                </div>

                {{-- List Produk --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 text-sm">Rincian Produk</h3>
                    </div>
                    <div class="p-6">
                        @foreach($details as $item)
                        <div class="flex gap-4 mb-6 last:mb-0 items-start">
                            <div class="w-20 h-20 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0 p-1">
                                @if($item->gambar) <img src="{{ asset('storage/' . $item->gambar) }}" class="w-full h-full object-contain">
                                @else <i class="fa-solid fa-box text-gray-300 text-2xl"></i> @endif
                            </div>

                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-sm line-clamp-2 mb-1">{{ $item->nama_produk }}</h4>
                                <p class="text-xs text-gray-500 mb-2">{{ $item->jumlah }} barang x Rp{{ number_format($item->harga_produk_saat_beli, 0, ',', '.') }}</p>

                                @if(strtolower($transaksi->status) == 'selesai')
                                @if(in_array($item->id_produk, $reviewedProductIds))
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100">
                                    <i class="fa-solid fa-check"></i> Sudah Diulas
                                </span>
                                @else
                                <button onclick="openReviewModal('{{ $item->id_produk }}', '{{ $item->nama_produk }}', '{{ $item->gambar ? asset('storage/' . $item->gambar) : '' }}')"
                                    class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-200 transition flex items-center gap-1 w-fit">
                                    <i class="fa-regular fa-star"></i> Beri Ulasan
                                </button>
                                @endif
                                @endif
                            </div>

                            <div class="text-right">
                                <p class="font-bold text-gray-800 text-sm">Rp{{ number_format($item->harga_produk_saat_beli * $item->jumlah, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Info Pengiriman --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    @if($transaksi->id_alamat)
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Info Pengiriman</h3>
                    <div class="flex gap-4 items-start">
                        <div class="mt-1"><i class="fa-solid fa-location-dot text-blue-600"></i></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Alamat Penerima</p>
                            @php
                            $alamatDb = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')->where('id_alamat', $transaksi->id_alamat)->first();
                            $detailAlamat = $alamatDb ? $alamatDb->detail_alamat . ' (' . $alamatDb->label . ')' : 'Alamat terhapus';
                            @endphp
                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $detailAlamat }}</p>
                        </div>
                    </div>
                    @else
                    <h3 class="font-bold text-orange-600 text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-store"></i> Ambil di Toko (Pickup)
                    </h3>
                    <div class="flex gap-4 items-start">
                        <div class="mt-1"><i class="fa-solid fa-qrcode text-orange-500 text-2xl"></i></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Kode Pengambilan: <span class="text-blue-600 text-lg ml-1">{{ $transaksi->kode_transaksi }}</span></p>
                            <p class="text-xs text-orange-600 mt-1 mb-3">Tunjukkan kode ini kepada kasir kami.</p>
                            <div class="bg-orange-50 border border-orange-100 p-3 rounded-lg">
                                <p class="text-xs font-bold text-gray-700">Épicerie Store (Pusat)</p>
                                <p class="text-xs text-gray-500">Jl. Ki Ageng Gribig, Klaten Utara, Jawa Tengah.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm sticky top-24">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 text-sm">Rincian Pembayaran</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between text-sm text-gray-600"><span>Metode Bayar</span><span class="font-bold text-gray-800">{{ $transaksi->metode_pembayaran }}</span></div>
                        <div class="flex justify-between text-sm text-gray-600"><span>Ongkos Kirim</span><span class="font-bold {{ $transaksi->ongkos_kirim > 0 ? 'text-gray-800' : 'text-green-600' }}">{{ $transaksi->ongkos_kirim > 0 ? 'Rp' . number_format($transaksi->ongkos_kirim, 0, ',', '.') : 'Gratis / Pickup' }}</span></div>
                        <hr class="border-dashed border-gray-200 my-2">
                        <div class="flex justify-between items-center"><span class="font-bold text-gray-800">Total Belanja</span><span class="font-extrabold text-xl text-blue-600">Rp{{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span></div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-b-xl border-t border-gray-100 flex flex-col gap-3">
                        <button onclick="window.print()" class="w-full border border-gray-300 bg-white text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-50 transition text-sm flex items-center justify-center gap-2"><i class="fa-solid fa-print"></i> Cetak Invoice</button>

                        @if(strtolower($transaksi->status) != 'selesai')
                        @if($transaksi->id_alamat)
                        <a href="{{ route('kiosk.tracking', $transaksi->id_transaksi) }}" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg hover:bg-orange-600 transition text-sm flex items-center justify-center gap-2">
                            <i class="fa-solid fa-map-location-dot"></i> Lacak Pesanan
                        </a>
                        @else
                        <form action="{{ route('kiosk.complete', $transaksi->id_transaksi) }}" method="POST" class="w-full" onsubmit="return confirm('Apakah Anda yakin sudah mengambil pesanan di toko?')">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition text-sm flex items-center justify-center gap-2">
                                <i class="fa-solid fa-box-check"></i> Selesai
                            </button>
                        </form>
                        @endif
                        @else
                        <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed text-sm flex items-center justify-center gap-2"><i class="fa-solid fa-check-circle"></i> Pesanan Selesai</button>
                        @endif

                        <a href="{{ route('kiosk.index') }}" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition text-sm flex items-center justify-center gap-2"><i class="fa-solid fa-bag-shopping"></i> Belanja Lagi</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL REVIEW YANG DIDESAIN ULANG (MIRIP TOKOPEDIA) --}}
    <div id="reviewModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReviewModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">

                <form action="{{ route('review.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_produk" id="modalProductId">

                    {{-- Header Modal --}}
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Berikan Ulasan</h3>
                        <button type="button" onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="bg-white px-6 py-6 sm:flex sm:gap-8">

                        {{-- Kiri: Form Input --}}
                        <div class="hidden sm:block w-64 bg-gray-50 p-4 rounded-xl h-fit border border-gray-100">
                            <h4 class="font-bold text-gray-700 text-sm mb-3 flex items-center gap-2">
                                <i class="fa-regular fa-lightbulb text-yellow-500"></i> Tips Menulis Ulasan
                            </h4>
                            <ul class="text-xs text-gray-500 space-y-3 list-disc pl-4">
                                <li><span class="font-bold text-gray-600">Kesesuaian:</span> Apakah produk sesuai dengan foto dan deskripsi?</li>
                                <li><span class="font-bold text-gray-600">Kualitas:</span> Bagaimana kualitas bahan, rasa, atau fungsinya?</li>
                                <li><span class="font-bold text-gray-600">Pengiriman:</span> Apakah pengemasan aman dan rapi?</li>
                            </ul>
                        </div>

                        <div class="flex-1">

                            {{-- Info Produk --}}
                            <div class="flex items-center gap-4 mb-6">
                                <img id="modalProductImg" src="" class="w-16 h-16 object-contain bg-white rounded-lg border border-gray-200 p-1">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm line-clamp-2 leading-tight mb-1" id="modalProductName">Nama Produk</p>
                                    <p class="text-xs text-gray-500">Bagaimana kualitas produk ini secara keseluruhan?</p>
                                </div>
                            </div>

                            {{-- Rating Bintang --}}
                            <div class="mb-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex gap-1" id="starContainer">
                                        @for($i=1; $i<=5; $i++)
                                            <label class="cursor-pointer transition-transform duration-200 hover:scale-110">
                                            <input type="radio" name="rating" value="{{ $i }}" class="hidden" onclick="fillStars({{ $i }})" required>
                                            <i id="star-icon-{{ $i }}" class="fa-solid fa-star text-4xl text-gray-300 transition-colors duration-200"></i>
                                            </label>
                                            @endfor
                                    </div>
                                    <span id="ratingLabel" class="text-sm font-bold text-gray-600 ml-2"></span>
                                </div>
                            </div>

                            {{-- Input Teks --}}
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Berikan ulasan untuk produk ini</label>
                                <textarea name="komentar" rows="4" class="w-full text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none transition" placeholder="Tulis deskripsi Anda mengenai produk ini..." required></textarea>
                                <p class="text-xs text-gray-400 mt-1 text-right">Min. 10 karakter</p>
                            </div>

                        </div>

                    </div>

                    {{-- Footer Actions --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeReviewModal()" class="px-6 py-2 bg-white text-gray-700 font-bold rounded-lg border border-gray-300 hover:bg-gray-50 transition text-sm">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 text-sm">
                            Kirim Ulasan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // --- 1. LOGIKA BINTANG (INTERAKTIF) ---
        const ratingTexts = {
            1: "Sangat Buruk",
            2: "Buruk",
            3: "Cukup",
            4: "Baik",
            5: "Sangat Baik"
        };

        function fillStars(rating) {
            // Update Warna Bintang
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
            // Update Label Teks
            const label = document.getElementById('ratingLabel');
            label.innerText = ratingTexts[rating] || "";
        }

        // --- 2. LOGIKA MODAL ---
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

            // Reset Form saat dibuka
            fillStars(0);
            const radios = document.getElementsByName('rating');
            for (const radio of radios) {
                radio.checked = false;
            }
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