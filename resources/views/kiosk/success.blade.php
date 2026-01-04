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
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20">

    @include('partials.navbar-kiosk')

    <div class="max-w-[1100px] mx-auto px-4 py-8">

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-6 text-sm font-bold shadow-sm" role="alert">
            <span class="block sm:inline"><i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}</span>
        </div>
        @endif

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

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Status Pesanan</p>
                        @php
                        $status = $transaksi->status ?? 'Dikemas';
                        $colorClass = 'text-yellow-600 bg-yellow-50 border-yellow-200';
                        $icon = 'fa-box-open';
                        // Logika Warna Status
                        if(strtolower($status) == 'dikirim') { $colorClass = 'text-blue-600 bg-blue-50 border-blue-200'; $icon = 'fa-truck-fast'; }
                        if(strtolower($status) == 'selesai') { $colorClass = 'text-green-600 bg-green-50 border-green-200'; $icon = 'fa-circle-check'; }
                        // Jika Pickup & Belum Selesai -> Status Siap Diambil
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

                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 text-sm">Rincian Produk</h3>
                    </div>
                    <div class="p-6">
                        @foreach($details as $item)
                        <div class="flex gap-4 mb-6 last:mb-0">
                            <div class="w-20 h-20 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0 p-1">
                                @if($item->gambar) <img src="{{ asset('storage/' . $item->gambar) }}" class="w-full h-full object-contain">
                                @else <i class="fa-solid fa-box text-gray-300 text-2xl"></i> @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-sm line-clamp-2 mb-1">{{ $item->nama_produk }}</h4>
                                <p class="text-xs text-gray-500 mb-2">{{ $item->jumlah }} barang x Rp{{ number_format($item->harga_produk_saat_beli, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800 text-sm">Rp{{ number_format($item->harga_produk_saat_beli * $item->jumlah, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

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
                            <p class="text-xs text-orange-600 mt-1 mb-3">Tunjukkan kode ini kepada kasir kami untuk mengambil pesanan.</p>
                            <div class="bg-orange-50 border border-orange-100 p-3 rounded-lg">
                                <p class="text-xs font-bold text-gray-700">Épicerie Store (Pusat)</p>
                                <p class="text-xs text-gray-500">Jl. Ki Ageng Gribig, Klaten Utara, Jawa Tengah.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

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
</body>

</html>