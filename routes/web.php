<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\Dashboard;

Route::get('/', function () {
    return view('welcome');
});

// Gunakan ini agar bisa langsung melihat hasil tanpa login
Route::get('/dashboard', [App\Http\Controllers\Dashboard::class, 'index']);
=======
// Panggil Controller yang baru kita buat biar dikenal
use App\Http\Controllers\KioskController; 

/*
|--------------------------------------------------------------------------
| Web Routes (Jalan Raya Aplikasi Épicerie)
|--------------------------------------------------------------------------
*/

// 1. Halaman Utama (Katalog Produk)
// Jadi pas buka http://localhost:8000 langsung muncul barang dagangan
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// 2. Aksi Tambah ke Keranjang
// URL-nya nanti kayak: /add-to-cart/5 (Artinya tambah barang ID 5)
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
// Pakai POST karena ngirim data form (metode pembayaran)
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');
>>>>>>> 960f8e587b6d1a775c0d8e430c975a71ae83c361
