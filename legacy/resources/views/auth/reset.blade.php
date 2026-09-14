<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ÈPICERIE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-blue-50 min-h-screen flex justify-center items-center font-sans p-6">

    <div class="bg-white p-10 rounded-3xl shadow-xl w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Reset Password</h2>
            <p class="text-gray-500 text-sm mt-2">Halo <b>{{ $user->nama }}</b>, silakan buat password baru yang aman.</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('password.reset.process') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="id_user" value="{{ $user->id_user }}">

            <div>
                <label class="block text-gray-600 text-xs font-bold mb-2 uppercase">Password Baru</label>
                <input type="password" name="password" class="w-full bg-gray-50 border-none rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-green-500 shadow-sm" placeholder="Minimal 6 karakter" required>
            </div>
            <div>
                <label class="block text-gray-600 text-xs font-bold mb-2 uppercase">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full bg-gray-50 border-none rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-green-500 shadow-sm" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-2xl hover:bg-blue-700 transition shadow-lg">
                Simpan Password Baru
            </button>
        </form>
    </div>
</body>
</html>