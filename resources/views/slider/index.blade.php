@extends('layouts.admin')

@section('title', 'Kelola Slider')
@section('header_title', 'Banner Slider')

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

    {{-- CONTAINER UTAMA --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[600px] relative border border-white/40">

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-gray-800 font-extrabold text-2xl tracking-tight">Kelola Banner</h2>
                <p class="text-gray-400 text-sm mt-1">Atur gambar promosi di halaman depan.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-sm border border-blue-100">
                Total: {{ $sliders->count() }} Slider
            </span>
        </div>

        {{-- Table Header (Desktop) --}}
        <div class="hidden md:flex px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
            <div class="w-32">Preview</div>
            <div class="flex-1">Info Promo</div>
            <div class="w-24 text-center">Urutan</div>
            <div class="w-32 text-center">Status</div>
            <div class="w-24 text-right">Aksi</div>
        </div>

        {{-- LIST SLIDER --}}
        <div class="flex flex-col gap-3">

            @forelse($sliders as $s)
            <div class="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300 relative overflow-hidden">

                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                {{-- Kolom 1: Gambar --}}
                <div class="flex items-center w-full md:w-32 mb-3 md:mb-0">
                    <div class="relative flex-shrink-0 w-full md:w-28 h-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm group-hover:shadow-md transition">
                        <img src="{{ asset('storage/' . $s->gambar) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </div>
                </div>

                {{-- Kolom 2: Info --}}
                <div class="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                    <h3 class="font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">
                        {{ $s->judul ?? 'Tanpa Judul' }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1 truncate max-w-md">
                        {{ $s->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}
                    </p>
                </div>

                {{-- Kolom 3: Urutan --}}
                <div class="w-full md:w-24 flex items-center md:justify-center mb-2 md:mb-0">
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 border border-gray-200 px-3 py-1 rounded-lg">
                        #{{ $s->urutan }}
                    </span>
                </div>

                {{-- Kolom 4: Status --}}
                <div class="w-full md:w-32 flex items-center md:justify-center mb-2 md:mb-0">
                    @if($s->is_active)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Aktif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-gray-50 text-gray-500 border border-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non-Aktif
                    </span>
                    @endif
                </div>

                {{-- Kolom 5: Aksi --}}
                <div class="flex items-center justify-end w-full md:w-24 gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300 transform md:translate-x-4 md:group-hover:translate-x-0">

                    {{-- TOMBOL EDIT MODAL --}}
                    <button type="button"
                        onclick="openEditModal(this)"
                        data-id="{{ $s->id_slider }}"
                        data-judul="{{ $s->judul }}"
                        data-deskripsi="{{ $s->deskripsi }}"
                        data-urutan="{{ $s->urutan }}"
                        data-active="{{ $s->is_active }}"
                        data-gambar="{{ asset('storage/' . $s->gambar) }}"
                        class="bg-white text-yellow-500 w-9 h-9 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 hover:bg-yellow-400 hover:text-white hover:border-yellow-400 transition"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>

                    <form action="{{ route('slider.destroy', $s->id_slider) }}" method="POST" onsubmit="return confirm('Hapus banner ini?');">
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
                    <i class="fa-regular fa-images text-4xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-600 text-lg">Belum ada slider.</h3>
                <p class="text-sm text-gray-400 mt-1">Tambahkan banner promosi untuk menarik pelanggan.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>

{{-- FAB Add Button --}}
<div class="fixed bottom-8 right-8 z-50">
    <a href="{{ route('slider.create') }}"
        class="bg-blue-600 text-white w-16 h-16 rounded-full hover:bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/40 transform hover:scale-110 hover:rotate-90 transition duration-300"
        title="Tambah Slider Baru">
        <i class="fa-solid fa-plus text-2xl"></i>
    </a>
</div>

{{-- MODAL EDIT SLIDER --}}
<div id="editModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>

    {{-- Modal Content --}}
    <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100 w-full max-w-3xl relative z-10 transform transition-all scale-100 max-h-[90vh] overflow-y-auto no-scrollbar">

        <div class="flex items-center justify-between mb-2 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Banner</h2>
                <p class="text-sm text-gray-400">Perbarui informasi tampilan banner.</p>
            </div>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition text-2xl">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form id="editForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-6">

                {{-- Preview Image Large --}}
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Gambar Banner</label>
                    <div class="relative w-full h-48 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 flex flex-col items-center justify-center overflow-hidden transition hover:border-blue-400 group cursor-pointer">

                        <img id="modal-preview-img" src="" class="absolute inset-0 w-full h-full object-cover z-10 hidden" />

                        <div id="modal-placeholder-icon" class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition z-0 pointer-events-none">
                            <i class="fa-solid fa-cloud-arrow-up text-4xl mb-2"></i>
                            <span class="text-sm font-bold">Ganti Gambar</span>
                            <span class="text-xs opacity-70">Klik untuk upload baru</span>
                        </div>

                        <input type="file" name="gambar" id="modal-input-file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Judul Promo</label>
                        <input type="text" name="judul" id="m_judul" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" placeholder="Opsional">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Urutan</label>
                        <input type="number" name="urutan" id="m_urutan" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" id="m_deskripsi" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" placeholder="Opsional">
                </div>

                <div>
                    <label class="inline-flex items-center cursor-pointer bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 w-full hover:bg-gray-100 transition">
                        <input type="checkbox" name="is_active" id="m_active" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-bold text-gray-700">Tampilkan Slider (Status Aktif)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end mt-8 pt-6 border-t border-gray-100 gap-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-500 hover:text-gray-700 font-bold">Batal</button>
                <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-xl font-bold hover:bg-yellow-600 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Update Slider
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
        const judul = button.dataset.judul;
        const deskripsi = button.dataset.deskripsi;
        const urutan = button.dataset.urutan;
        const active = button.dataset.active;
        const gambar = button.dataset.gambar;

        // 2. Set Action Form (FIXED: Menggunakan Placeholder)
        let url = "{{ route('slider.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;

        // 3. Isi Input
        document.getElementById('m_judul').value = judul;
        document.getElementById('m_deskripsi').value = deskripsi;
        document.getElementById('m_urutan').value = urutan;

        // Handle Checkbox
        // Pastikan active bernilai 1 atau true untuk di-check
        document.getElementById('m_active').checked = (active == 1 || active == '1');

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