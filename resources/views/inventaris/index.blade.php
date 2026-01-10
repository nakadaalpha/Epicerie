@extends('layouts.admin')

@section('title', 'Inventaris Barang')
@section('header_title', 'Manajemen Inventaris')

@section('content')

{{-- Style Khusus --}}
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
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

    {{-- --- BAGIAN 1: FILTER & PENCARIAN (TIDAK BERUBAH) --- --}}
    <div class="mb-8">
        <form action="{{ route('inventaris.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            {{-- Search --}}
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Produk..."
                    class="w-full p-3.5 pl-12 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40">
                <div class="absolute left-4 top-3.5 text-blue-500">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('inventaris.index') }}" class="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-times text-lg"></i>
                </a>
                @endif
            </div>

            {{-- Filter Kategori --}}
            <div class="relative min-w-[180px]">
                <select name="kategori" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $kat)
                    <option value="{{ $kat->id_kategori }}" {{ request('kategori') == $kat->id_kategori ? 'selected' : '' }}>
                        {{ $kat->nama_kategori }}
                    </option>
                    @endforeach
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none"><i class="fa-solid fa-filter"></i></div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs"><i class="fa-solid fa-chevron-down"></i></div>
            </div>

            {{-- Sorting --}}
            <div class="relative min-w-[180px]">
                <select name="sort" onchange="this.form.submit()"
                    class="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer appearance-none">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                    <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
                    <option value="stok_sedikit" {{ request('sort') == 'stok_sedikit' ? 'selected' : '' }}>Stok Paling Sedikit</option>
                    <option value="stok_banyak" {{ request('sort') == 'stok_banyak' ? 'selected' : '' }}>Stok Paling Banyak</option>
                    <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Harga Tertinggi</option>
                </select>
                <div class="absolute left-3.5 top-3.5 text-blue-500 pointer-events-none"><i class="fa-solid fa-arrow-down-up-across-line"></i></div>
                <div class="absolute right-3.5 top-4 text-gray-400 pointer-events-none text-xs"><i class="fa-solid fa-chevron-down"></i></div>
            </div>
        </form>
    </div>

    {{-- --- BAGIAN 2: LIST PRODUK --- --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Header Tabel Desktop --}}
        <div class="hidden md:flex items-center px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-20">Produk</div>
            <div class="flex-1">Nama & Kategori</div>
            <div class="w-32">Harga</div>
            <div class="w-24 text-center">Stok</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        <div class="flex flex-col gap-3">
            @forelse($produk as $p)
            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                {{-- Gambar --}}
                <div class="flex items-center w-full md:w-20 mb-3 md:mb-0">
                    <div class="relative flex-shrink-0">
                        @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" class="w-12 h-12 rounded-lg object-cover shadow-sm border border-gray-200">
                        @else
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-bold shadow-sm border border-blue-100">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        @endif
                        @if($p->stok < 10)
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 border-2 border-white rounded-full animate-pulse" title="Stok Kritis">
                    </div>
                    @endif
                </div>
                <div class="md:hidden ml-3">
                    <h3 class="font-bold text-gray-800 text-sm">{{ $p->nama_produk }}</h3>
                </div>
            </div>

            {{-- Detail --}}
            <div class="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                <h3 class="hidden md:block font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">{{ $p->nama_produk }}</h3>
                <div class="flex items-center mt-1">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $p->kategori ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-red-50 text-red-500 border-red-100' }}">
                        {{ $p->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                    </span>
                </div>
            </div>

            {{-- Harga --}}
            <div class="w-full md:w-32 flex items-center mb-2 md:mb-0">
                <span class="font-extrabold text-blue-600 text-sm">Rp {{ number_format($p->harga_produk, 0, ',', '.') }}</span>
            </div>

            {{-- Stok --}}
            <div class="w-full md:w-24 flex md:justify-center items-center mb-2 md:mb-0">
                <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $p->stok < 10 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }}">
                    {{ $p->stok }} Unit
                </span>
            </div>

            {{-- Aksi --}}
            <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">

                {{-- TOMBOL EDIT MODAL (Diubah dari <a> menjadi <button>) --}}
                <button type="button"
                    onclick="openEditModal(this)"
                    data-id="{{ $p->id_produk }}"
                    data-nama="{{ $p->nama_produk }}"
                    data-kategori="{{ $p->id_kategori }}"
                    data-stok="{{ $p->stok }}"
                    data-harga="{{ $p->harga_produk }}"
                    data-deskripsi="{{ $p->deskripsi_produk }}"
                    data-gambar="{{ $p->gambar ? asset('storage/' . $p->gambar) : '' }}"
                    class="bg-white text-yellow-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-yellow-400 hover:text-white hover:border-yellow-400 transition"
                    title="Edit">
                    <i class="fa-solid fa-pen text-xs"></i>
                </button>

                <form action="{{ route('produk.destroy', $p->id_produk) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?');" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-white text-red-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-gray-200 hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                        title="Hapus">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
            <div class="bg-gray-50 p-6 rounded-full mb-4">
                <i class="fa-solid fa-magnifying-glass-minus text-4xl text-gray-300"></i>
            </div>
            <h3 class="font-bold text-gray-600 text-lg">Tidak ada data.</h3>
        </div>
        @endforelse
    </div>

    @if($produk->hasPages())
    <div class="mt-8">{{ $produk->links() }}</div>
    @endif
</div>
</div>

{{-- Tombol Tambah Produk (Tetap pakai halaman terpisah/sesuai kebutuhan) --}}
<div class="fixed bottom-8 right-8 z-50">
    <a href="{{ route('produk.create') }}"
        class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
        title="Tambah Produk Baru">
        <i class="fa-solid fa-plus text-2xl"></i>
    </a>
</div>

{{-- --- BAGIAN 3: MODAL EDIT PRODUK --- --}}
<div id="editModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>

    {{-- Modal Content (Menggunakan Styling Edit Produk Asli) --}}
    <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100 w-full max-w-4xl relative z-10 transform transition-all scale-100 max-h-[90vh] overflow-y-auto custom-scrollbar">

        <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Data Produk</h2>
                <p class="text-sm text-gray-400">Perbarui informasi produk di sini.</p>
            </div>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition text-2xl">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        {{-- Form Edit --}}
        <form id="editForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Kolom Gambar --}}
                <div class="md:col-span-1">
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Foto Produk</label>
                    <div id="drop-zone" class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300 group cursor-pointer hover:border-blue-400 hover:bg-blue-50">

                        {{-- Image Preview --}}
                        <img id="modal-preview-img" src="" class="absolute inset-0 w-full h-full object-cover z-10 hidden" />

                        {{-- Placeholder --}}
                        <div id="modal-placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none px-4 text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-5xl mb-3 transition-transform duration-300"></i>
                            <span class="text-sm font-bold">Ganti Gambar</span>
                            <span class="text-xs font-normal mt-1 opacity-70">Klik untuk upload</span>
                        </div>

                        <input type="file" name="gambar" id="modal-input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Biarkan kosong jika gambar tidak berubah.</p>
                </div>

                {{-- Kolom Input Data --}}
                <div class="md:col-span-2 space-y-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Produk</label>
                        <input type="text" name="nama_produk" id="m_nama" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Kategori</label>
                            <select name="id_kategori" id="m_kategori" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 cursor-pointer" required>
                                <option value="" disabled>Pilih Kategori</option>
                                @foreach($kategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Stok</label>
                            <input type="number" name="stok" id="m_stok" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Harga Satuan</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                            <input type="number" name="harga_produk" id="m_harga" class="w-full p-3 pl-10 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi</label>
                        <textarea name="deskripsi_produk" id="m_deskripsi" rows="3" class="w-full p-4 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Batal</button>
                <button type="submit" class="bg-yellow-500 text-white px-8 py-3 rounded-xl hover:bg-yellow-600 flex items-center font-bold">
                    <i class="fa-solid fa-pen-to-square"></i>Update Produk
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT LOGIC MODAL --}}
<script>
    function openEditModal(button) {
        // 1. Ambil data dari tombol yang diklik
        const id = button.dataset.id;
        const nama = button.dataset.nama;
        const kategori = button.dataset.kategori;
        const stok = button.dataset.stok;
        const harga = button.dataset.harga;
        const deskripsi = button.dataset.deskripsi;
        const gambar = button.dataset.gambar;

        // 2. Set Action Form URL
        // Trik: Gunakan string ':id' sebagai placeholder agar Laravel tidak error saat render
        let url = "{{ route('produk.update', ':id') }}";

        // Replace string ':id' dengan ID asli dari tombol menggunakan JavaScript
        url = url.replace(':id', id);

        const form = document.getElementById('editForm');
        form.action = url;

        // 3. Isi Inputan
        document.getElementById('m_nama').value = nama;
        document.getElementById('m_kategori').value = kategori;
        document.getElementById('m_stok').value = stok;
        document.getElementById('m_harga').value = harga;
        document.getElementById('m_deskripsi').value = deskripsi;

        // 4. Handle Preview Gambar
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

    // Logic Upload Preview di dalam Modal
    const modalInputFile = document.getElementById('modal-input-file');
    const modalPreviewImg = document.getElementById('modal-preview-img');
    const modalPlaceholder = document.getElementById('modal-placeholder-icon');

    modalInputFile.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                modalPreviewImg.src = e.target.result;
                modalPreviewImg.classList.remove('hidden');
                modalPlaceholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>

@endsection