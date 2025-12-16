<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-6xl">

        <div class="bg-white rounded-3xl p-8 shadow-2xl min-h-[600px] relative">

            <div class="flex justify-between items-center mb-8">
                <button class="flex items-center space-x-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl shadow-sm hover:bg-gray-50 transition">
                    <i class="fa-regular fa-calendar"></i>
                    <span class="font-medium text-sm">Minggu Ini</span>
                    <i class="fa-solid fa-chevron-down text-xs ml-2 text-gray-400"></i>
                </button>

                <div class="flex items-center text-gray-500 bg-gray-100 px-4 py-2 rounded-full shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                    <span class="font-bold text-sm">Riwayat Transaksi</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-400 text-xs font-bold uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4">No</th>
                            <th class="p-4">Kode</th>
                            <th class="p-4">Tanggal & Waktu</th>
                            <th class="p-4">Kasir</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Payment</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 font-medium">
                        @forelse($transaksi as $index => $trx)
                        <tr class="hover:bg-blue-50 transition duration-200 border-b border-gray-50 last:border-0 group">
                            <td class="p-4">{{ $index + 1 }}.</td>
                            <td class="p-4 font-bold text-gray-800">{{ $trx->kode_transaksi }}</td>
                            <td class="p-4 text-gray-500">
                                {{ $trx->tanggal_transaksi->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">
                                        {{ substr($trx->kasir->nama ?? '?', 0, 1) }}
                                    </div>
                                    {{ $trx->kasir->nama ?? 'Sistem' }}
                                </div>
                            </td>
                            <td class="p-4 text-gray-800">
                                Rp. {{ number_format($trx->total_bayar, 0, ',', '.') }}
                            </td>
                            <td class="p-4">
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-semibold">
                                    {{ $trx->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="flex items-center text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold w-fit">
                                    <i class="fa-solid fa-check-circle mr-1"></i> Paid
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <button class="text-gray-400 hover:text-blue-600 transition">
                                    <span class="text-xs font-bold mr-1 group-hover:underline">Detail</span>
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fa-solid fa-receipt text-4xl mb-2 opacity-30"></i>
                                    <p>Belum ada transaksi minggu ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center text-xs text-gray-400 px-2">
                <span>Menampilkan {{ $transaksi->count() }} data</span>
                <div class="flex space-x-2">
                    <button class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg font-bold">1</button>

                    <button class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

</body>

</html>