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
        <div class="flex flex-col items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <img src="https://assets.tokopedia.net/assets-tokopedia-lite/v2/zeus/kratos/a6fa58a6.png" class="w-48 opacity-80 mb-4" alt="Empty Cart">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Wah, keranjang belanjamu kosong</h2>
            <p class="text-gray-500 mb-6">Yuk, isi dengan barang-barang impianmu!</p>
            <a href="{{ route('kiosk.index') }}" class="bg-blue-600 text-white font-bold py-2.5 px-10 rounded-lg shadow hover:bg-blue-700 transition">
                Mulai Belanja
            </a>
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