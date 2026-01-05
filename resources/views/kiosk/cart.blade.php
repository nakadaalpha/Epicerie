<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        /* Custom Checkbox */
        .custom-checkbox {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 0.375rem;
            border: 2px solid #cbd5e1;
            appearance: none;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .custom-checkbox:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .custom-checkbox:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            font-size: 0.75rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-700">

    @include('partials.navbar-kiosk')

    <div id="confirm-modal" class="fixed inset-0 z-[999] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 transition-transform">
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Hapus item?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">Item ini akan dihapus dari keranjangmu.</p>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="w-full py-2.5 rounded-lg border border-gray-300 font-bold hover:bg-gray-50 text-sm">Batal</button>
                <a id="confirm-btn-link" href="#" class="w-full py-2.5 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 text-center text-sm">Hapus</a>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="max-w-[1150px] mx-auto px-4 mt-6">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Perhatian!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="max-w-[1150px] mx-auto px-4 py-8">
        <h1 class="font-bold text-2xl mb-6 text-gray-800">Keranjang</h1>

        @if($keranjang->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 px-4">

            <div class="relative mb-6 group">
                <div class="absolute inset-0 bg-blue-100 rounded-full animate-ping opacity-75"></div>
                <div class="relative w-32 h-32 bg-blue-50 rounded-full flex items-center justify-center border-4 border-white shadow-sm group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-basket-shopping text-5xl text-blue-400"></i>
                </div>
            </div>

            <h2 class="text-2xl font-extrabold text-gray-800 mb-2">Keranjang Masih Kosong</h2>
            <p class="text-gray-500 mb-8 text-center max-w-md leading-relaxed">
                Belum ada barang nih. Yuk isi dengan kebutuhan harianmu!
            </p>

            <a href="{{ route('kiosk.index') }}" class="group relative inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white transition-all duration-200 bg-blue-600 border border-transparent rounded-full hover:bg-blue-700 shadow-lg shadow-blue-500/30 hover:-translate-y-1 mb-12">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Mulai Belanja
            </a>

            <div class="w-full max-w-4xl border-t border-gray-200 pt-10 relative">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-50 px-4 text-gray-400 text-xs font-bold uppercase tracking-widest">
                    Mungkin Anda Suka
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                    @foreach($rekomendasi as $p)
                    @php
                    $hasDiskon = ($p->persen_diskon > 0);
                    $hargaFinal = $hasDiskon
                    ? $p->harga_produk - ($p->harga_produk * ($p->persen_diskon / 100))
                    : $p->harga_produk;
                    @endphp

                    <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md hover:border-orange-200 relative group overflow-hidden">

                        @if($hasDiskon)
                        <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-tags"></i> Hemat {{ $p->persen_diskon }}%
                        </div>
                        @endif

                        <a href="{{ route('produk.show', $p->id_produk) }}" class="block flex-1 cursor-pointer">
                            <div class="aspect-square rounded-xl mb-3 flex items-center justify-center overflow-hidden relative">
                                @if($p->gambar)
                                <img src="{{ asset('storage/' . $p->gambar) }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition duration-300">
                                @else
                                <span class="text-4xl">📦</span>
                                @endif
                            </div>

                            <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 truncate">{{ $p->nama_produk }}</h3>

                            @if($hasDiskon)
                            <div class="flex flex-col items-start mb-1">
                                <span class="text-[10px] text-gray-400 line-through decoration-red-400">
                                    Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                                </span>
                                <span class="text-orange-600 font-extrabold text-sm block">
                                    Rp{{ number_format($hargaFinal, 0, ',', '.') }}
                                </span>
                            </div>
                            @else
                            <span class="text-orange-600 font-extrabold text-sm mb-1 block">
                                Rp{{ number_format($p->harga_produk, 0, ',', '.') }}
                            </span>
                            @endif
                        </a>

                        <div class="flex justify-end mt-2 z-20 relative">
                            @if($p->stok > 0)
                            <a href="{{ route('kiosk.add', $p->id_produk) }}" class="bg-orange-500 hover:bg-orange-600 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md active:scale-90 transition">
                                <i class="fa-solid fa-plus"></i>
                            </a>
                            @else
                            <span class="text-xs text-red-500 font-bold py-1 bg-red-50 px-2 rounded-lg">Habis</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 relative">

            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="check-all" class="custom-checkbox" checked onchange="toggleCheckAll(this)">
                            <label for="check-all" class="font-bold text-gray-700 cursor-pointer select-none">Pilih Semua ({{ count($keranjang) }})</label>
                        </div>
                        <a href="{{ route('kiosk.empty') }}" onclick="return confirm('Kosongkan keranjang?')" class="text-blue-600 font-bold text-sm hover:text-blue-800">Hapus</a>
                    </div>

                    <div>
                        @foreach($keranjang as $item)
                        <div class="p-6 border-b border-gray-50 last:border-0">
                            <div class="flex gap-4 w-full">
                                <div class="flex gap-4 shrink-0 items-start">
                                    <div class="pt-2">
                                        <input type="checkbox" class="custom-checkbox item-checkbox" checked onchange="updateCheckAllState()">
                                    </div>
                                    <div class="w-20 h-20 bg-gray-50 rounded-md border border-gray-200 flex items-center justify-center overflow-hidden">
                                        @if($item->produk->gambar)
                                        <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-contain">
                                        @else
                                        <span class="text-2xl">📦</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex-1 flex flex-col sm:flex-row justify-between items-start gap-4">
                                    <div class="flex flex-col gap-1 pr-4">
                                        <a href="{{ route('produk.show', $item->id_produk) }}" class="font-bold text-gray-800 text-sm line-clamp-2 hover:text-blue-600 leading-snug">
                                            {{ $item->produk->nama_produk }}
                                        </a>
                                        @if($item->produk->persen_diskon > 0)
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded">{{ $item->produk->persen_diskon }}%</span>
                                            <span class="text-xs text-gray-400 line-through">Rp{{ number_format($item->produk->harga_produk, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="font-extrabold text-gray-900 text-base mt-1">
                                            Rp{{ number_format($item->produk->harga_final, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="flex flex-row sm:flex-col items-end justify-between w-full sm:w-auto gap-4 sm:gap-6 mt-2 sm:mt-0">
                                        <div class="flex items-center border border-gray-300 rounded-lg h-8 bg-white shadow-sm w-24 order-1 sm:order-1">
                                            <a href="{{ route('kiosk.decrease', $item->id_produk) }}" class="w-8 h-full flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-gray-50 rounded-l-lg transition {{ $item->jumlah <= 1 ? 'pointer-events-none opacity-50' : '' }}">
                                                <i class="fa-solid fa-minus text-xs"></i>
                                            </a>
                                            <input type="text" value="{{ $item->jumlah }}" class="flex-1 w-full text-center text-sm font-bold text-gray-700 border-none bg-transparent p-0 focus:ring-0 cursor-default" readonly>
                                            <a href="{{ route('kiosk.increase', $item->id_produk) }}" class="w-8 h-full flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-r-lg transition">
                                                <i class="fa-solid fa-plus text-xs"></i>
                                            </a>
                                        </div>

                                        <div class="flex items-center gap-3 order-2 sm:order-2">
                                            <button class="text-gray-400 hover:text-pink-500 transition p-1" title="Pindahkan ke Wishlist">
                                                <i class="fa-regular fa-heart"></i>
                                            </button>
                                            <button onclick="openModal('{{ route('kiosk.remove', $item->id_produk) }}')" class="text-gray-400 hover:text-gray-600 transition p-1" title="Hapus Barang">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white p-5 rounded-xl shadow-[0_1px_6px_rgba(0,0,0,0.1)] border border-gray-100 sticky top-28">
                    <h3 class="font-bold text-gray-800 text-lg mb-4">Ringkasan belanja</h3>

                    <div class="flex justify-between items-center mb-4 text-gray-600 text-sm">
                        <span>Total Harga ({{ count($keranjang) }} barang)</span>
                        <span class="font-bold text-gray-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-gray-100 my-4"></div>

                    <div class="border border-gray-200 rounded-lg p-3 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition mb-6 group">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-ticket text-blue-600 group-hover:scale-110 transition"></i>
                            <span class="text-sm font-bold text-gray-700">Makin hemat pakai promo</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 text-xs"></i>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="font-bold text-lg text-gray-800">Total Belanja</span>
                        <span class="font-extrabold text-xl text-blue-600">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('kiosk.checkout') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-center transition active:scale-95 text-sm shadow-lg shadow-blue-200">
                        Beli ({{ count($keranjang) }})
                    </a>
                </div>
            </div>

        </div>
        @endif
    </div>

    <script>
        function toggleCheckAll(source) {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function updateCheckAllState() {
            const checkAll = document.getElementById('check-all');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkAll.checked = allChecked;
            checkAll.indeterminate = checkboxes.length > 0 && Array.from(checkboxes).some(cb => cb.checked) && !allChecked;
        }

        function openModal(url) {
            document.getElementById('confirm-modal').classList.remove('hidden');
            document.getElementById('confirm-btn-link').href = url;
        }

        function closeModal() {
            document.getElementById('confirm-modal').classList.add('hidden');
        }
    </script>

</body>

</html>