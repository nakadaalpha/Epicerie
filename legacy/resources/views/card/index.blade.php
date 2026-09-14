@extends('layouts.admin')

@section('title', 'Permintaan Cetak Kartu')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Antrean Cetak Kartu Member</h2>
        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
            {{ $requests->count() }} Permintaan
        </span>
    </div>

    @if($requests->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <i class="fa-regular fa-folder-open text-4xl mb-3"></i>
        <p>Tidak ada permintaan cetak kartu saat ini.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                    <th class="p-4">Tanggal Request</th>
                    <th class="p-4">Member</th>
                    <th class="p-4">Level</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($requests as $req)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 text-sm text-gray-500">
                        {{ $req->updated_at->format('d M Y H:i') }}
                    </td>
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">
                                {{ substr($req->nama, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $req->nama }}</p>
                                <p class="text-xs text-gray-500">{{ $req->username }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase
                                {{ $req->membership == 'Gold' ? 'bg-yellow-100 text-yellow-700' : 
                                  ($req->membership == 'Silver' ? 'bg-gray-100 text-gray-700' : 'bg-orange-100 text-orange-700') }}">
                            {{ $req->membership }}
                        </span>
                    </td>
                    <td class="p-4 flex justify-center gap-2">
                        {{-- 1. TOMBOL CETAK PDF --}}
                        <a href="{{ route('admin.card.print', $req->id_user) }}" target="_blank"
                            class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition flex items-center gap-1">
                            <i class="fa-solid fa-print"></i> Cetak PDF
                        </a>

                        {{-- 2. TOMBOL SELESAI --}}
                        <form action="{{ route('admin.card.complete', $req->id_user) }}" method="POST" onsubmit="return confirm('Tandai kartu ini sudah dicetak dan dikirim?')">
                            @csrf
                            <button type="submit" class="bg-green-100 text-green-700 border border-green-200 px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-200 transition flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Selesai
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection