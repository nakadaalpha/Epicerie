<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen font-sans">

    @include('partials.navbar')

    <div class="container mx-auto p-6 flex justify-center items-center min-h-[80vh]">

        <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-lg">

            <div class="flex items-center mb-6 text-blue-600">
                <a href="{{ route('karyawan.index') }}" class="mr-4 hover:text-blue-800 transition">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </a>
                <h2 class="text-2xl font-bold">Edit Data Karyawan</h2>
            </div>

            <form action="{{ route('karyawan.update', $karyawan->id_user) }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ $karyawan->nama }}"
                        class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-gray-50" required>
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2">Username</label>
                    <input type="text" name="username" value="{{ $karyawan->username }}"
                        class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-gray-50" required>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-700 font-semibold mb-2">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Isi hanya jika ingin mengganti password"
                        class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-gray-50">
                    <div class="mt-2 flex items-center text-xs text-gray-400">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        <span>Biarkan kosong jika password tidak berubah.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('karyawan.index') }}" class="text-gray-500 hover:text-gray-700 font-medium transition">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-6 py-3 rounded-full hover:bg-yellow-600 transition font-bold shadow-lg flex items-center">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>