<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Edit Karyawan</h2>

        <form action="{{ route('karyawan.update', $karyawan->id_user) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" value="{{ $karyawan->nama }}" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" value="{{ $karyawan->username }}" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Password Baru (Opsional)</label>
                <input type="password" name="password" class="w-full border p-2 rounded" placeholder="Isi jika ingin ganti password">
                <small class="text-gray-500">Kosongkan jika tidak ingin mengganti password.</small>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('karyawan.index') }}" class="text-gray-500 mt-2">Batal</a>
                <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
            </div>
        </form>
    </div>
</body>
</html>