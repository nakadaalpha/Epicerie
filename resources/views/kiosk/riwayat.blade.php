@extends('layouts.customer')

@section('title', 'Riwayat Belanja')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fa-solid fa-bag-shopping text-blue-600"></i> Pesanan Saya</h1>

{{-- Filter Tab --}}
<div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-1 no-scrollbar">
    <a href="{{ route('kiosk.riwayat') }}" class="{{ !request('status') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600' }} px-4 py-2 text-sm transition">Semua</a>
    <a href="{{ route('kiosk.riwayat', ['status' => 'diproses']) }}" class="{{ request('status') == 'diproses' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-6 py-3 rounded-t-xl transition whitespace-nowrap text-sm"">Diproses</a>
    <a href=" {{ route('kiosk.riwayat', ['status' => 'Dikirim']) }}" class="{{ request('status') == 'Dikirim' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-6 py-3 rounded-t-xl transition whitespace-nowrap text-sm"">Dikirim</a>
    <a href=" {{ route('kiosk.riwayat', ['status' => 'Selesai']) }}" class="{{ request('status') == 'Selesai' ? 'text-blue-600 font-bold border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600 font-medium hover:bg-gray-100' }} px-6 py-3 rounded-t-xl transition whitespace-nowrap text-sm"">Selesai</a>
</div>

@if($riwayat->isEmpty())
<div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100">
    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-200"><i class="fa-solid fa-receipt text-4xl"></i></div>
    <h3 class="text-lg font-bold text-gray-800">Tidak ada pesanan</h3>
    <a href="{{ route('kiosk.index') }}" class="mt-4 inline-block bg-blue-600 text-white font-bold py-2.5 px-8 rounded-xl hover:bg-blue-700 transition">Mulai Belanja</a>
</div>
@else
<div class="space-y-3">
    @foreach($riwayat as $trx)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between md:items-center gap-3">
            <div>
                <span class="font-bold text-gray-800 block text-sm">{{ $trx->kode_transaksi }}</span>
                <span class="text-xs text-gray-500">{{ date('d M Y, H:i', strtotime($trx->created_at)) }}</span>
            </div>
            @php
            $status = $trx->status ?? 'diproses';
            $bg = 'bg-yellow-100 text-yellow-700';
            if(strtolower($status) == 'dikirim') $bg = 'bg-blue-100 text-blue-700';
            if(strtolower($status) == 'selesai') $bg = 'bg-green-100 text-green-700';
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $bg }}">{{ strtoupper($status) }}</span>
        </div>
        <div class="p-6">
            @foreach($trx->detailTransaksi as $detail)
            <div class="flex gap-4 mb-4 last:mb-0">
                <div class="w-16 h-16 bg-white rounded-lg border border-gray-100 p-1 shrink-0">
                    @if($detail->produk && $detail->produk->gambar) <img src="{{ asset('storage/' . $detail->produk->gambar) }}" class="w-full h-full object-contain">
                    @else <i class="fa-solid fa-box text-gray-300 text-2xl flex items-center justify-center h-full"></i> @endif
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $detail->produk->nama_produk ?? 'Produk Dihapus' }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $detail->jumlah }} x Rp{{ number_format($detail->harga_produk_saat_beli, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
            <div><span class="text-[10px] text-gray-400 uppercase font-bold">Total</span>
                <p class="font-bold text-blue-600">Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('kiosk.success', $trx->id_transaksi) }}" class="px-5 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 transition">Detail</a>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection