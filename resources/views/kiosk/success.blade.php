<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Sukses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-blue-50 h-screen flex flex-col items-center justify-center p-6 text-center">

    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-sm transform transition-all scale-100">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
            <i class="fa-solid fa-check text-4xl text-green-500"></i>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-2">Transaksi Berhasil!</h2>
        <p class="text-gray-500 mb-8">Terima kasih sudah berbelanja di Épicerie.</p>

        <div class="space-y-3">
            <a href="{{ route('kiosk.index') }}" class="block w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">
                Belanja Lagi
            </a>
        </div>
    </div>

    <p class="mt-8 text-gray-400 text-sm">© 2025 Épicerie Kiosk</p>

</body>
</html>