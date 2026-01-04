<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="space-y-4">

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-wider">Info Penerima</h4>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $t->user->nama ?? 'Guest' }}</p>
                    <p class="text-xs text-gray-500">{{ $t->user->no_hp ?? '-' }}</p>

                    @php
                    $alamatTampil = 'Ambil di Toko (Pickup)';
                    $isPickup = true;
                    if($t->id_alamat) {
                    $isPickup = false;
                    $addr = DB::table('alamat_pengiriman')->where('id_alamat', $t->id_alamat)->first();
                    $alamatTampil = $addr ? $addr->detail_alamat : 'Alamat tidak ditemukan';
                    }
                    @endphp
                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 p-2 rounded border border-gray-100">
                        <i class="fa-solid {{ $isPickup ? 'fa-store text-orange-500' : 'fa-location-dot text-red-500' }} mr-1"></i>
                        {{ $alamatTampil }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-fit">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-bold text-gray-500 uppercase">Metode Pembayaran</span>
                <span class="text-xs font-bold bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $t->metode_pembayaran }}</span>
            </div>

            <div class="flex justify-between items-end mt-2">
                <span class="text-sm text-gray-600">Total Tagihan (COD)</span>
                @if($t->metode_pembayaran == 'Tunai')
                <span class="text-xl font-extrabold text-blue-500">Rp{{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                @else
                <div class="text-right">
                    <span class="text-lg font-bold text-gray-400 line-through block">Rp0</span>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">LUNAS (Non-Tunai)</span>
                </div>
                @endif
            </div>

            @if($t->metode_pembayaran == 'Tunai')
            <div class="mt-3 bg-yellow-50 text-yellow-800 text-[10px] p-2 rounded border border-yellow-100 flex gap-2 items-start">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Pastikan menagih uang tunai sesuai nominal di atas.</span>
            </div>
            @endif
        </div>

    </div>

    <div class="flex flex-col h-full">

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex-1 mb-4">
            <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-wider">Daftar Barang ({{ count($details) }})</h4>

            <div class="space-y-3 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
                @foreach($details as $item)
                <div class="flex gap-3 items-center border-b border-gray-50 last:border-0 pb-3 last:pb-0">
                    <div class="w-12 h-12 bg-gray-50 rounded-md flex items-center justify-center shrink-0 border border-gray-100">
                        @if($item->gambar)
                        <img src="{{ asset('storage/' . $item->gambar) }}" class="w-full h-full object-contain p-0.5">
                        @else
                        <i class="fa-solid fa-box text-gray-300"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ $item->nama_produk }}</p>
                        <p class="text-xs text-gray-500">{{ $item->jumlah }} pcs x Rp{{ number_format($item->harga_produk_saat_beli, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-gray-700">Rp{{ number_format($item->harga_produk_saat_beli * $item->jumlah, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-dashed border-gray-200">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Subtotal Produk</span>
                    <span>Rp{{ number_format($t->total_bayar - $t->ongkos_kirim, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Ongkos Kirim</span>
                    <span>Rp{{ number_format($t->ongkos_kirim, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div>
            @if($t->status != 'selesai')

            @if(!$t->id_alamat)
            {{-- TOMBOL KHUSUS PICKUP --}}
            <form action="{{ route('kurir.selesai', $t->id_transaksi) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Konfirmasi pesanan ini sudah diambil oleh customer?')" class="w-full bg-green-500 text-white font-bold py-3 rounded-xl hover:bg-green-600 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-box-check text-lg"></i>
                    <span>Konfirmasi Pesanan Diambil</span>
                </button>
            </form>

            @elseif($t->status == 'Dikirim')
            {{-- TOMBOL SELESAI DELIVERY --}}
            <form action="{{ route('kurir.selesai', $t->id_transaksi) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Pesanan sudah sampai dan diterima?')" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check-double text-lg"></i>
                    <span>Selesaikan Pengantaran</span>
                </button>
            </form>

            @else
            {{-- TOMBOL MULAI ANTAR (Jika status Dikemas) --}}
            <form action="{{ route('kurir.mulai', $t->id_transaksi) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-blue-500 text-white font-bold py-3 rounded-xl hover:bg-blue-600 transition  flex items-center justify-center gap-2">
                    <i class="fa-solid fa-motorcycle text-lg"></i>
                    <span>Mulai Antar Pesanan</span>
                </button>
            </form>
            @endif

            @else
            <div class="w-full bg-gray-100 text-gray-400 font-bold py-3 rounded-xl text-center cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fa-solid fa-check-circle"></i> Transaksi Selesai
            </div>
            @endif
        </div>

    </div>
</div>