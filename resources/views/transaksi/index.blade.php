<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap'); body { font-family: 'Nunito', sans-serif; }</style>
</head>
<body class="bg-gray-50 font-sans text-gray-700">

    <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-2xl font-extrabold text-blue-600">ÉPICERIE ADMIN</a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-bold text-gray-500">Manajemen Pesanan</span>
        </div>
        <div class="flex gap-6 text-sm font-bold text-gray-600">
            <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
            <a href="{{ route('transaksi.index') }}" class="text-blue-600">Pesanan Masuk</a>
        </div>
    </nav>

    <div class="max-w-[95%] mx-auto px-4 py-10">
        
        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Pesanan</h1>
                <p class="text-gray-500 mt-1">Kelola status pesanan pelanggan di sini.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs font-extrabold tracking-wider">
                    <tr>
                        <th class="px-6 py-5">Info Transaksi</th>
                        <th class="px-6 py-5">Pelanggan</th>
                        <th class="px-6 py-5 bg-blue-50 text-blue-600">Dikirim Oleh</th> <th class="px-6 py-5">Barang</th>
                        <th class="px-6 py-5">Total</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-6 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($transaksi as $trx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800 block mb-1">{{ $trx->kode_transaksi }}</span>
                            <span class="text-gray-400 text-xs"><i class="fa-regular fa-clock mr-1"></i> {{ date('d M Y, H:i', strtotime($trx->created_at)) }}</span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $trx->user->nama ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500">{{ $trx->user->no_hp ?? '-' }}</div>
                        </td>

                        <td class="px-6 py-4 bg-blue-50/30">
                            @if($trx->kurir)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                        {{ substr($trx->kurir->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-700 text-xs">{{ $trx->kurir->nama }}</div>
                                        <div class="text-[10px] text-gray-500 font-bold bg-white px-1 rounded border border-gray-200 inline-block">Karyawan</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                    <i class="fa-solid fa-hourglass-start"></i> Belum Dikirim
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @foreach($trx->detailTransaksi as $detail)
                                <div class="text-xs text-gray-600">
                                    <span class="font-bold">{{ $detail->jumlah }}x</span> {{ Str::limit($detail->produk->nama_produk ?? 'Produk Dihapus', 20) }}
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-6 py-4 font-bold text-blue-600 text-base">
                            Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $status = $trx->status;
                                $color = 'bg-gray-100 text-gray-600 border-gray-200';
                                if($status == 'Dikemas') $color = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                                if($status == 'Dikirim') $color = 'bg-blue-50 text-blue-700 border-blue-200';
                                if($status == 'Selesai') $color = 'bg-green-50 text-green-700 border-green-200';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                {{ strtoupper($status) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($trx->status == 'Dikemas')
                                <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="Dikirim">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm shadow-blue-200 transition flex items-center gap-2 mx-auto">
                                        <i class="fa-solid fa-truck-fast"></i> Kirim Barang
                                    </button>
                                </form>

                            @elseif($trx->status == 'Dikirim')
                                <form action="{{ route('transaksi.update', $trx->id_transaksi) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="Selesai">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm shadow-green-200 transition flex items-center gap-2 mx-auto">
                                        <i class="fa-solid fa-check"></i> Selesaikan
                                    </button>
                                </form>

                            @else
                                <span class="text-gray-300 text-xs italic"><i class="fa-solid fa-lock"></i> Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                            <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                            <p>Belum ada pesanan masuk.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>