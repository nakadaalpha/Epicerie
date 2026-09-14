<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-md"> <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Inventaris Produk</h1>
            <a href="{{ route('produk.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i> Tambah Produk
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-3 border-b">No</th>
                    <th class="p-3 border-b">Nama Produk</th>
                    <th class="p-3 border-b">Deskripsi</th> <th class="p-3 border-b">Kategori</th>
                    <th class="p-3 border-b">Harga</th>
                    <th class="p-3 border-b">Stok</th>
                    <th class="p-3 border-b text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produk as $index => $p)
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-3">{{ $index + 1 }}</td>
                    <td class="p-3 font-medium">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">📦</span>
                            {{ $p->nama_produk }}
                        </div>
                    </td>
                    
                    <td class="p-3 text-sm text-gray-500 italic max-w-xs">
                        {{ Str::limit($p->deskripsi_produk, 40) }}
                    </td>

                    <td class="p-3">
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                            {{ $p->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                        </span>
                    </td>
                    <td class="p-3">Rp{{ number_format($p->harga_produk, 0, ',', '.') }}</td>
                    <td class="p-3 {{ $p->stok < 10 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                        {{ $p->stok }} pcs
                    </td>
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ route('produk.edit', $p->id_produk) }}" class="text-yellow-500 hover:text-yellow-600"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a href="{{ route('produk.hapus', $p->id_produk) }}" class="text-red-500 hover:text-red-600" onclick="return confirm('Hapus produk ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>