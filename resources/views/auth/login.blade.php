<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-input:focus-within { transform: scale(1.02); }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-500 to-teal-400 min-h-screen flex justify-center items-center font-sans p-6">

    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden">

        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-blue-50 rounded-full opacity-50 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 bg-teal-50 rounded-full opacity-50 blur-2xl"></div>

        <div class="relative z-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#3b4bbd]/10 text-[#3b4bbd] rounded-full mb-4">
                    <i class="fa-solid fa-store text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">ÈPICERIE</h2>
                <p class="text-gray-400 text-sm mt-2">Masuk untuk mengelola toko Anda</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-4 rounded-xl mb-6 text-sm flex items-start shadow-sm">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 mr-2"></i>
                <div>
                    <span class="font-bold block">Gagal Masuk!</span>
                    {{ $errors->first() }}
                </div>
            </div>
            @endif
            
            @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-600 p-4 rounded-xl mb-6 text-sm flex items-start shadow-sm">
                <i class="fa-solid fa-circle-check mt-0.5 mr-2"></i>
                <div>
                    <span class="font-bold block">Sukses!</span>
                    {{ session('success') }}
                </div>
            </div>
            @endif

            <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-5">
                @csrf

                <div class="form-input transition-all duration-300">
                    <label class="block text-gray-600 text-xs font-bold mb-2 ml-1 uppercase tracking-wide">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="username" value="{{ old('username') }}" class="w-full bg-gray-50 text-gray-800 border-none rounded-2xl py-3.5 pl-11 pr-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white transition duration-200 shadow-inner" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="form-input transition-all duration-300">
                    <label class="block text-gray-600 text-xs font-bold mb-2 ml-1 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" class="w-full bg-gray-50 text-gray-800 border-none rounded-2xl py-3.5 pl-11 pr-4 focus:ring-2 focus:ring-[#3b4bbd] focus:bg-white transition duration-200 shadow-inner" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#3b4bbd] text-white font-bold py-3.5 rounded-2xl shadow-lg hover:bg-blue-800 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 mt-4 flex justify-center items-center group">
                    <span>Masuk Aplikasi</span>
                    <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </button>

                <div class="flex items-center justify-between text-sm text-gray-500 pt-2">
                    <label class="flex items-center cursor-pointer hover:text-gray-700">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-[#3b4bbd] rounded border-gray-300 focus:ring-[#3b4bbd]">
                        <span class="ml-2">Ingat Saya</span>
                    </label>
                    <a href="{{ route('password.forgot') }}" class="hover:text-[#3b4bbd] transition font-bold">Lupa Password?</a>
                </div>

                <div class="mt-6 text-center border-t border-gray-100 pt-4">
                    <p class="text-gray-500 text-sm">Belum terdaftar?</p>
                    <a href="{{ route('register') }}" class="text-[#3b4bbd] font-bold hover:underline transition">
                        Buat Akun Baru
                    </a>
                </div>
            </form>
        </div>
        <div class="text-center mt-8 text-gray-400 text-xs">
            &copy; {{ date('Y') }} ÈPICERIE POS System
        </div>
    </div>
</body>
</html>