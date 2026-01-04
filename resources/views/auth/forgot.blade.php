<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex justify-center items-center font-sans p-6">

    <div class="bg-white p-10 rounded-3xl shadow-xl w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Verifikasi Keamanan</h2>
            <p class="text-gray-500 text-sm mt-2">Masukkan data akun untuk mereset password.</p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-4 rounded-xl mb-6 text-sm flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('password.verify') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-600 text-xs font-bold mb-2 uppercase">Username</label>
                <input type="text" name="username" class="w-full bg-gray-50 border-none rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-blue-500 shadow-sm" required>
            </div>
            <div>
                <label class="block text-gray-600 text-xs font-bold mb-2 uppercase">Nomor HP</label>
                <input type="number" name="no_hp" class="w-full bg-gray-50 border-none rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-blue-500 shadow-sm" required>
            </div>
            
            <div>
                <label class="block text-blue-600 text-xs font-bold mb-2 uppercase">PIN Keamanan (6 Digit)</label>
                <input type="password" name="pin_keamanan" class="w-full bg-blue-50 border border-blue-100 rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-blue-500 shadow-sm text-center tracking-widest font-bold text-lg text-blue-800" placeholder="• • • • • •" maxlength="6" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-2xl hover:bg-blue-700 transition shadow-lg mt-2">
                Verifikasi & Reset
            </button>
            
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">
                    Lupa PIN Keamanan? <br>
                    <a href="https://wa.me/6281234567890" target="_blank" class="font-bold text-blue-600 hover:underline">
                        <i class="fa-brands fa-whatsapp mr-1"></i> Hubungi Admin Toko
                    </a>
                </p>
            </div>

            <div class="text-center mt-4 pt-2">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-blue-600 font-bold">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>