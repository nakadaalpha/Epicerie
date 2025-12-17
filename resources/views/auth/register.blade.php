<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen flex justify-center items-center font-sans p-6">

    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden">

        <div class="text-center mb-6 z-10 relative">
            <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h2>
            <p class="text-gray-400 text-sm mt-1">Bergabunglah dengan ÈPICERIE</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-4 text-xs">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.proses') }}" method="POST" class="space-y-4 relative z-10">
            @csrf

            <div>
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                    class="w-full bg-gray-50 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white border-none shadow-inner" required>
            </div>

            <div>
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="w-full bg-gray-50 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white border-none shadow-inner" required>
            </div>

            <div>
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Password</label>
                <input type="password" name="password"
                    class="w-full bg-gray-50 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white border-none shadow-inner" required>
            </div>

            <div>
                <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Ulangi Password</label>
                <input type="password" name="password_confirmation"
                    class="w-full bg-gray-50 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white border-none shadow-inner" required>
            </div>

            <button type="submit" class="w-full bg-[#3b4bbd] text-white font-bold py-3 rounded-2xl shadow-lg hover:bg-blue-800 transition mt-2">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-6 relative z-10">
            <p class="text-gray-500 text-sm">Sudah punya akun?</p>
            <a href="{{ route('login') }}" class="text-[#3b4bbd] font-bold hover:underline">Login disini</a>
        </div>
    </div>
</body>

</html>