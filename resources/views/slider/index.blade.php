<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Slider - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 max-w-5xl">
        <div class="bg-white rounded-3xl p-8 shadow-2xl min-h-[600px] relative">

            @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 flex items-center animate-pulse">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
            @endif

            <div class="flex justify-between items-center mb-6 ml-1">
                <h2 class="text-blue-500 font-bold text-xl">Kelola Banner Slider</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Total: {{ $sliders->count() }}</span>
            </div>

            <div class="grid grid-cols-1 gap-6">
                @forelse($sliders as $s)
                <div class="flex flex-col md:flex-row items-center p-4 bg-gray-50 rounded-2xl hover:bg-blue-50 transition duration-300 border border-transparent hover:border-blue-100 group relative">

                    <div class="w-full md:w-48 h-24 rounded-xl overflow-hidden border-2 border-white shadow-sm flex-shrink-0">
                        <img src="{{ asset('storage/' . $s->gambar) }}" class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1 mt-4 md:mt-0 md:ml-6 w-full text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-2 mb-1">
                            <span class="bg-blue-100 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded">Urutan: {{ $s->urutan }}</span>
                            @if($s->is_active)
                            <span class="bg-green-100 text-green-600 text-[10px] font-bold px-2 py-0.5 rounded">Aktif</span>
                            @else
                            <span class="bg-gray-200 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded">Non-Aktif</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">{{ $s->judul ?? 'Tanpa Judul' }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $s->deskripsi ?? '-' }}</p>
                    </div>

                    <div class="flex space-x-2 mt-4 md:mt-0 md:opacity-0 group-hover:opacity-100 transition-all duration-300 md:absolute md:right-4 bg-white/80 backdrop-blur-sm p-1.5 rounded-full shadow-sm">
                        <a href="{{ route('slider.edit', $s->id_slider) }}" class="bg-white text-yellow-500 w-9 h-9 rounded-full flex items-center justify-center shadow hover:bg-yellow-50 transition">
                            <i class="fa-solid fa-pen text-sm"></i>
                        </a>
                        <form action="{{ route('slider.destroy', $s->id_slider) }}" method="POST" onsubmit="return confirm('Yakin hapus slider ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-white text-red-500 w-9 h-9 rounded-full flex items-center justify-center shadow hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center text-gray-400 py-10">
                    <i class="fa-regular fa-images text-4xl mb-3"></i>
                    <p>Belum ada slider yang ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="absolute bottom-8 right-8 z-10">
                <a href="{{ route('slider.create') }}" class="bg-[#3b4bbd] text-white w-14 h-14 rounded-full hover:bg-blue-800 flex items-center justify-center transform hover:scale-110 hover:rotate-90 transition duration-300 shadow-lg">
                    <i class="fa-solid fa-plus text-2xl"></i>
                </a>
            </div>
        </div>
    </div>
</body>

</html>