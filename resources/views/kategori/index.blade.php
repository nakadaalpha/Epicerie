@extends('layouts.admin')

@section('title', 'Kategori Produk')
@section('header_title', 'Manajemen Kategori')

@section('content')

{{-- Style Khusus --}}
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Animasi Modal */
    #editModal {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    #editModal.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    #editModal:not(.hidden) {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
</style>

<div class="max-w-7xl mx-auto">

    {{-- SECTION 1: SEARCH --}}
    <div class="mb-8 max-w-2xl">
        <form action="{{ route('kategori.index') }}" method="GET" class="relative flex items-center gap-4">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Kategori..."
                    class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40">
                <div class="absolute left-4 top-3.5 text-blue-500">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('kategori.index') }}" class="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition" title="Reset">
                    <i class="fa-solid fa-times text-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- SECTION 2: LIST KATEGORI --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Daftar Kategori</h2>
                <p class="text-gray-400 text-sm mt-1">Kelompokkan produk agar mudah ditemukan.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                Total: {{ $kategori->total() }} Data
            </span>
        </div>

        {{-- Table Header --}}
        <div class="hidden md:flex items-center px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-20">Ikon</div>
            <div class="flex-1">Nama Kategori</div>
            <div class="w-32 text-center">Jumlah Produk</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        <div class="flex flex-col gap-3">
            @forelse($kategori as $k)
            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                {{-- Kolom 1: Ikon --}}
                <div class="flex items-center w-full md:w-20 mb-3 md:mb-0">
                    <div class="relative flex-shrink-0">
                        @if($k->gambar)
                        <img src="{{ asset('storage/' . $k->gambar) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm border border-gray-200 bg-white">
                        @else
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg border border-blue-200 shadow-sm">
                            {{ substr(strtoupper($k->nama_kategori), 0, 1) }}
                        </div>
                        @endif
                    </div>
                    {{-- Mobile View Title --}}
                    <div class="md:hidden ml-3 flex-1">
                        <h3 class="font-bold text-gray-800 text-base">{{ $k->nama_kategori }}</h3>
                        <span class="text-xs text-gray-500">{{ $k->produk_count ?? 0 }} Produk</span>
                    </div>
                </div>

                {{-- Kolom 2: Nama --}}
                <div class="hidden md:block flex-1 min-w-0 pr-4">
                    <h3 class="font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">{{ $k->nama_kategori }}</h3>
                </div>

                {{-- Kolom 3: Count --}}
                <div class="hidden md:flex w-32 justify-center items-center">
                    <span class="text-xs font-bold px-3 py-1 rounded-full border {{ ($k->produk_count ?? 0) > 0 ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                        {{ $k->produk_count ?? 0 }} Item
                    </span>
                </div>

                {{-- Kolom 4: Aksi --}}
                <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">

                    {{-- TOMBOL EDIT (MODAL) --}}
                    <button type="button"
                        onclick="openEditModal(this)"
                        data-id="{{ $k->id_kategori }}"
                        data-nama="{{ $k->nama_kategori }}"
                        data-gambar="{{ $k->gambar ? asset('storage/' . $k->gambar) : '' }}"
                        class="bg-white text-yellow-500 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 hover:bg-yellow-400 hover:text-white hover:border-yellow-400 transition"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>

                    <form action="{{ route('kategori.destroy', $k->id_kategori) }}" method="POST" onsubmit="return confirm('Hapus kategori ini? Produk di dalamnya mungkin akan kehilangan kategori.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-white text-red-500 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                            title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>

                </div>
            </div>
            @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                    <i class="fa-solid fa-layer-group text-4xl text-gray-300"></i>
                </div>
                @if(request('search'))
                <h3 class="font-bold text-gray-600 text-lg">Kategori tidak ditemukan.</h3>
                <a href="{{ route('kategori.index') }}" class="mt-2 text-sm font-bold text-blue-500 hover:underline">Reset Pencarian</a>
                @else
                <h3 class="font-bold text-gray-600 text-lg">Belum ada kategori.</h3>
                <p class="text-sm text-gray-400 mt-1">Tambahkan kategori baru sekarang.</p>
                @endif
            </div>
            @endforelse
        </div>

        @if(method_exists($kategori, 'links'))
        <div class="mt-8">
            {{ $kategori->links() }}
        </div>
        @endif

    </div>
</div>

{{-- Tombol Tambah --}}
<div class="fixed bottom-8 right-8 z-50">
    <a href="{{ route('kategori.create') }}"
        class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
        title="Tambah Kategori Baru">
        <i class="fa-solid fa-plus text-2xl"></i>
    </a>
</div>

{{-- SECTION 3: MODAL EDIT --}}
<div id="editModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>

    {{-- Modal Content --}}
    <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100 w-full max-w-2xl relative z-10 transform transition-all scale-100">

        <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Kategori</h2>
                <p class="text-sm text-gray-400">Perbarui nama dan ikon kategori.</p>
            </div>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition text-2xl">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form id="editForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="flex flex-col md:flex-row gap-8">
                {{-- Upload Gambar --}}
                <div class="w-full md:w-1/3 flex flex-col items-center">
                    <label class="block text-gray-700 font-bold mb-2 text-sm w-full text-left">Ikon</label>
                    <div class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition hover:border-blue-400 group cursor-pointer">

                        <img id="modal-preview-img" src="" class="absolute inset-0 w-full h-full object-cover z-10 hidden" />

                        <div id="modal-placeholder-icon" class="text-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none">
                            <i class="fa-solid fa-cloud-arrow-up text-4xl mb-2"></i>
                            <p class="text-xs font-bold">Ganti Gambar</p>
                        </div>

                        <input type="file" name="gambar" id="modal-input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    </div>
                </div>

                {{-- Input Fields --}}
                <div class="w-full md:w-2/3 space-y-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="m_nama" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex gap-3 text-sm text-blue-700">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <p>Perubahan nama kategori akan berlaku untuk semua produk terkait.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8 pt-6 border-t border-gray-100 gap-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-500 hover:text-gray-700 font-bold">Batal</button>
                <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-xl font-bold hover:bg-yellow-600">
                    <i class="fa-solid fa-pen-to-square"></i>Update Kategori
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    function openEditModal(button) {
        // 1. Ambil Data
        const id = button.dataset.id;
        const nama = button.dataset.nama;
        const gambar = button.dataset.gambar;

        // 2. Set Action Form (Menggunakan Placeholder Trick)
        let url = "{{ route('kategori.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;

        // 3. Isi Input
        document.getElementById('m_nama').value = nama;

        // 4. Handle Preview Image
        const preview = document.getElementById('modal-preview-img');
        const placeholder = document.getElementById('modal-placeholder-icon');

        if (gambar) {
            preview.src = gambar;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        // 5. Tampilkan Modal
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Listener Preview Upload
    document.getElementById('modal-input-file').addEventListener('change', function() {
        const [file] = this.files;
        if (file) {
            document.getElementById('modal-preview-img').src = URL.createObjectURL(file);
            document.getElementById('modal-preview-img').classList.remove('hidden');
            document.getElementById('modal-placeholder-icon').classList.add('hidden');
        }
    });
</script>

@endsection