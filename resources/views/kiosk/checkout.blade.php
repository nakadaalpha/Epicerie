<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }

        /* Custom Checkbox Biru */
        .custom-checkbox {
            accent-color: #2563eb;
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1100px] mx-auto px-4 py-8">

        <h1 class="font-bold text-3xl mb-5">Keranjang</h1>

        <form action="{{ route('kiosk.pay') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-8">

                    <div class="bg-white rounded-xl overflow-hidden">

                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="selectAll" class="custom-checkbox" checked onclick="toggleSelectAll(this)">

                                <label for="selectAll" class="font-bold text-gray-700 text-base cursor-pointer select-none">
                                    Pilih Semua <span class="text-gray-400 font-normal">({{ count($keranjang) }})</span>
                                </label>
                            </div>
                            <button type="button" class="text-blue-600 font-bold text-sm hover:text-blue-800 transition">Hapus</button>
                        </div>

                        <div class="p-6">

                            <div class="flex items-center gap-3 mb-6">
                                <input type="checkbox" class="custom-checkbox item-checkbox" checked onclick="checkSelectAllStatus()">

                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-store text-blue-600"></i>
                                    <span class="font-bold text-gray-800 text-sm">Épicerie Pusat</span>
                                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded ml-1 tracking-wide">Official Store</span>
                                </div>
                            </div>

                            <div class="space-y-8">
                                @foreach($keranjang as $item)
                                <div class="flex gap-4 items-start group relative">

                                    <div class="mt-8 shrink-0">
                                        <input type="checkbox" name="selected_items[]" value="{{ $item->id_produk }}" class="custom-checkbox item-checkbox" checked onclick="checkSelectAllStatus()">
                                    </div>

                                    <div class="w-24 h-24 bg-white rounded-lg border border-gray-200 flex items-center justify-center shrink-0 overflow-hidden relative p-1 mt-1">
                                        @if($item->produk->gambar)
                                        <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-contain hover:scale-105 transition duration-300">
                                        @else
                                        <i class="fa-solid fa-box text-gray-300 text-2xl"></i>
                                        @endif

                                        @if(rand(0,1))
                                        <div class="absolute top-0 left-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-br shadow-sm z-10">
                                            {{ rand(10,50) }}%
                                        </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0 flex flex-col justify-between h-full">

                                        <div class="flex justify-between items-start gap-4">
                                            <h3 class="text-gray-700 font-medium text-sm leading-snug line-clamp-2 pt-1 hover:text-blue-600 cursor-pointer transition" title="{{ $item->produk->nama_produk }}">
                                                {{ $item->produk->nama_produk }}
                                            </h3>

                                            <div class="text-right shrink-0">
                                                <span class="block font-bold text-gray-900 text-base">Rp{{ number_format($item->produk->harga_produk, 0, ',', '.') }}</span>
                                                @if(rand(0,1))
                                                <span class="block text-xs text-gray-400 line-through mt-0.5">Rp{{ number_format($item->produk->harga_produk * 1.2, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="h-4"></div>

                                        <div class="flex justify-end items-center gap-3 mt-auto">
                                            <button type="button" class="text-gray-400 hover:text-red-500 transition p-1.5 rounded-full hover:bg-red-50" title="Wishlist">
                                                <i class="fa-solid fa-heart text-lg"></i>
                                            </button>

                                            <a href="{{ route('kiosk.remove', $item->id_produk) }}" class="text-gray-400 hover:text-gray-600 transition p-1.5 mr-2 rounded-full hover:bg-gray-100" onclick="return confirm('Hapus item ini?')" title="Hapus">
                                                <i class="fa-regular fa-trash-can text-lg"></i>
                                            </a>

                                            <div class="flex items-center border border-gray-300 rounded-full h-8 w-[100px] overflow-hidden bg-white shadow-sm hover:border-blue-400 transition group/qty">
                                                <a href="{{ route('kiosk.decrease', $item->id_produk) }}" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition">
                                                    <i class="fa-solid fa-minus text-xs"></i>
                                                </a>
                                                <input type="text" value="{{ $item->jumlah }}" class="flex-1 w-full h-full text-center text-sm font-bold text-gray-700 border-none focus:ring-0 p-0 cursor-default group-hover/qty:text-blue-600 transition" readonly>
                                                <a href="{{ route('kiosk.increase', $item->id_produk) }}" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition">
                                                    <i class="fa-solid fa-plus text-xs"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                @if(!$loop->last)
                                <div class="border-b border-gray-100 w-full"></div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-4">
                    <div class="bg-white p-6 rounded-xl sticky top-24">
                        <h3 class="font-bold text-gray-800 mb-5 text-base">Ringkasan belanja</h3>

                        <div class="border border-green-200 bg-green-50 rounded-lg p-3 flex items-center justify-between cursor-pointer hover:bg-green-100 transition mb-5 group shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 bg-green-200 rounded-full flex items-center justify-center text-green-700 shadow-sm">
                                    <i class="fa-solid fa-ticket text-xs"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-700 group-hover:text-green-800 transition">Makin hemat pakai promo</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-gray-400 text-xs group-hover:text-green-600 transition"></i>
                        </div>

                        <div class="space-y-3 mb-5">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Total Harga ({{ count($keranjang) }} barang)</span>
                                <span>Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Total Diskon Barang</span>
                                <span class="text-green-600 font-medium">-Rp0</span>
                            </div>
                        </div>

                        <hr class="border-gray-200 border-dashed my-5">

                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-lg text-gray-800">Total Belanja</span>
                            <span class="font-extrabold text-xl text-blue-600">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-blue-700 hover:shadow-blue-500/30 transition transform active:scale-[0.98] flex justify-center items-center gap-2">
                            <span>Beli ({{ count($keranjang) }})</span>
                        </button>

                        <div class="mt-4 text-center">
                            <p class="text-[10px] text-gray-400 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-shield-halved"></i> Jaminan aman & garansi uang kembali
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <script>
        // Fungsi Select All
        function toggleSelectAll(source) {
            // Ambil semua checkbox dengan class 'item-checkbox'
            checkboxes = document.getElementsByClassName('item-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                // Set status checked sama dengan checkbox utama (source)
                checkboxes[i].checked = source.checked;
            }
        }

        // Fungsi Cek Status Select All (jika user uncheck salah satu item manual)
        function checkSelectAllStatus() {
            var checkboxes = document.getElementsByClassName('item-checkbox');
            var selectAll = document.getElementById('selectAll');
            var allChecked = true;

            // Loop untuk cek apakah ada satu saja yang tidak dicentang
            for (var i = 0; i < checkboxes.length; i++) {
                if (!checkboxes[i].checked) {
                    allChecked = false;
                    break;
                }
            }
            // Update status checkbox utama
            selectAll.checked = allChecked;
        }
    </script>

</body>

</html>