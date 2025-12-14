<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Karyawan</h1>
            <a href="{{ route('karyawan.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i> Tambah Karyawan
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-3 border-b">No</th>
                    <th class="p-3 border-b">Nama Lengkap</th>
                    <th class="p-3 border-b">Username</th>
                    <th class="p-3 border-b">Tanggal Masuk</th>
                    <th class="p-3 border-b text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($karyawan as $index => $k)
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-3">{{ $index + 1 }}</td>
                    <td class="p-3 font-medium">{{ $k->nama }}</td>
                    <td class="p-3 text-gray-500">{{ $k->username }}</td>
                    <td class="p-3 text-gray-500">{{ $k->created_at->format('d M Y') }}</td>
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ route('karyawan.edit', $k->id_user) }}" class="text-yellow-500 hover:text-yellow-600">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="{{ route('karyawan.hapus', $k->id_user) }}" class="text-red-500 hover:text-red-600" onclick="return confirm('Yakin mau hapus karyawan ini?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach

                @if($karyawan->isEmpty())
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-400">Belum ada data karyawan</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

</body>
</html>