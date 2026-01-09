@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('header_title', 'Manajemen Karyawan')

@section('content')
    <div class="max-w-7xl mx-auto">

        <div class="mb-8 max-w-lg">
            <form action="{{ route('karyawan.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Username..."
                    class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40">
                <div class="absolute left-4 top-3.5 text-blue-500">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-[2rem] p-8 shadow-2xl border border-white/40 min-h-[500px]">

            @if(session('success'))
            <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check text-xl mr-3"></i> {{ session('success') }}
            </div>
            @endif

            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Daftar Karyawan</h2>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full shadow-sm border border-blue-100">
                    Total: {{ $karyawan->count() }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($karyawan as $k)
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-lg transition duration-300 group relative">
                    <div class="flex items-center w-full">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-4 font-bold text-lg border-2 border-indigo-50">
                            {{ substr($k->nama, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-800 capitalize text-sm md:text-base truncate">{{ $k->nama }}</h3>
                            <div class="flex flex-col text-xs text-gray-500 mt-0.5">
                                <span class="font-bold text-indigo-500 uppercase tracking-wide">{{ $k->role ?? 'STAFF' }}</span>
                                <span class="text-gray-400">@ {{ $k->username }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full block shadow-sm"></span>
                    </div>

                    @if(Auth::user()->role == 'Pemilik')
                    <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 absolute right-3 top-1/2 -translate-y-1/2 translate-x-4 group-hover:translate-x-0 bg-white/80 backdrop-blur-sm p-1 rounded-full shadow-sm">
                        <a href="{{ route('karyawan.edit', $k->id_user) }}" class="bg-white text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-yellow-400 hover:text-white transition">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                        @if($k->id_user != Auth::id())
                        <a href="{{ route('karyawan.hapus', $k->id_user) }}" onclick="return confirm('Hapus karyawan?')" class="bg-white text-red-500 w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:bg-red-500 hover:text-white transition">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
                @empty
                <div class="col-span-full py-16 text-center text-gray-400">
                    <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Belum ada karyawan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @if(Auth::user()->role == 'Pemilik')
    <div class="fixed bottom-8 right-8 z-50">
        <a href="{{ route('karyawan.create') }}" class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300">
            <i class="fa-solid fa-plus text-2xl"></i>
        </a>
    </div>
    @endif
@endsection