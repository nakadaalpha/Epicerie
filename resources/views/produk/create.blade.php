<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Tambah Produk Baru</h2>

        <form action="{{ route('produk.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700">Nama Produk</label>
                <input type="text" name="nama_produk" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Kategori</label>
                <select name="id_kategori" class="w-full border p-2 rounded" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700">Harga (Rp)</label>
                    <input type="number" name="harga_produk" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700">Stok Awal</label>
                    <input type="number" name="stok" class="w-full border p-2 rounded" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Deskripsi</label>
                <textarea name="deskripsi_produk" class="w-full border p-2 rounded" rows="3"></textarea>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('produk.index') }}" class="text-gray-500 mt-2">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Produk</button>
            </div>
        </form>
    </div>
</body>
</html>