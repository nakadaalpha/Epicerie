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

    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden">
        
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h2>
            <p class="text-gray-400 text-xs mt-1">Bergabunglah dengan ÈPICERIE</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-xs">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.proses') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-600 text-[10px] font-bold mb-1 uppercase">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-600 text-[10px] font-bold mb-1 uppercase">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-600 text-[10px] font-bold mb-1 uppercase">Nomor HP</label>
                    <input type="number" name="no_hp" value="{{ old('no_hp') }}" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" placeholder="08..." required>
                </div>
                <div>
                    <label class="block text-blue-600 text-[10px] font-bold mb-1 uppercase">PIN Keamanan (6 Angka)</label>
                    <input type="number" name="pin_keamanan" value="{{ old('pin_keamanan') }}" class="w-full bg-blue-50 border border-blue-100 rounded-xl py-3 px-4 text-sm font-bold text-blue-800 focus:ring-2 focus:ring-blue-500 placeholder-blue-300" placeholder="123456" required>
                </div>
            </div>

            <div>
                <label class="block text-gray-600 text-[10px] font-bold mb-1 uppercase">Password</label>
                <input type="password" name="password" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-600 text-[10px] font-bold mb-1 uppercase">Ulangi Password</label>
                <input type="password" name="password_confirmation" class="w-full bg-gray-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-[#3b4bbd] text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-800 transition mt-2">
                Daftar Sekarang
            </button>

            <div class="text-center mt-4">
                <p class="text-gray-400 text-xs">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="text-[#3b4bbd] font-bold text-sm hover:underline">Login disini</a>
            </div>
        </form>
    </div>
</body>
</html>