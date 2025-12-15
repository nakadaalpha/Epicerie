<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KioskController; 
use App\Http\Controllers\Dashboard;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/admin', [App\Http\Controllers\Dashboard::class, 'index']);

// 1. Halaman Utama (Katalog Produk)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// 2. Aksi Tambah ke Keranjang
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');
