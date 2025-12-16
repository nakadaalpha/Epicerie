<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - ÈPICERIE</title>
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

    <div class="container mx-auto p-6 max-w-5xl">

        <div class="mb-6 relative">
            <form action="{{ route('karyawan.index') }}" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau Username..."
                    class="w-full p-3 pl-5 rounded-full shadow-lg outline-none focus:ring-2 focus:ring-blue-300 text-gray-600 transition bg-white/90 backdrop-blur-sm">
                <button type="submit" class="absolute right-5 top-3.5 text-gray-400 hover:text-blue-500 transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-2xl min-h-[600px] relative">

            @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 flex items-center animate-pulse">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
            @endif

            <div class="flex justify-between items-center mb-6 ml-1">
                <h2 class="text-blue-500 font-bold text-xl">Daftar Karyawan</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Total: {{ $karyawan->count() }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                @forelse($karyawan as $k)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-blue-50 transition duration-300 border border-transparent hover:border-blue-100 group relative">

                    <div class="flex items-center w-full">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-4 font-bold border-2 border-white shadow-sm text-lg flex-shrink-0">
                            {{ substr($k->nama, 0, 1) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-800 capitalize text-sm md:text-base truncate">{{ $k->nama }}</h3>

                            <div class="flex flex-col text-xs text-gray-500 mt-1 space-y-0.5">
                                <span class="font-medium text-indigo-500 uppercase tracking-wider text-[10px]">
                                    {{ $k->role ?? 'STAFF' }}
                                </span>
                                <span class="flex items-center text-gray-400">
                                    <i class="fa-solid fa-user-tag text-[10px] mr-1"></i> {{ $k->username }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-2 right-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full block" title="Aktif"></span>
                    </div>

                    <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0 absolute right-4 bg-white/80 backdrop-blur-sm p-1 rounded-full shadow-sm">
                        <a href="{{ route('karyawan.edit', $k->id_user) }}" class="bg-white text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center shadow hover:bg-yellow-50 transition" title="Edit">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                        <a href="{{ route('karyawan.hapus', $k->id_user) }}" onclick="return confirm('Hapus karyawan ini?')" class="bg-white text-red-500 w-8 h-8 rounded-full flex items-center justify-center shadow hover:bg-red-50 transition" title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </a>
                    </div>
                </div>
                @empty
                @endforelse
            </div>

            @if($karyawan->isEmpty())
            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 pointer-events-none">
                <div class="bg-gray-100 p-6 rounded-full mb-4 animate-bounce">
                    <i class="fa-solid fa-users-slash text-4xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-500 text-lg">Data Karyawan Kosong.</h3>
                <p class="text-sm opacity-60">Belum ada karyawan yang terdaftar.</p>
            </div>
            @endif

            <div class="absolute bottom-8 right-8 z-10">
                <a href="{{ route('karyawan.create') }}"
                    class="bg-[#3b4bbd] text-white w-14 h-14 rounded-full hover:bg-blue-800 flex items-center justify-center transform hover:scale-110 hover:rotate-90 transition duration-300"
                    title="Tambah Karyawan Baru">
                    <i class="fa-solid fa-plus text-2xl"></i>
                </a>
            </div>

        </div>
    </div>

</body>

</html>