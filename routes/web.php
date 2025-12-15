<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\KioskController; 
=======
//<<<<<<< HEAD
>>>>>>> c805f41a3d1ab147aba1b0d8b973b51148702e1d
use App\Http\Controllers\Dashboard;

// Route::get('/', function () {
//     return view('welcome');
// });

<<<<<<< HEAD
Route::get('/admin', [App\Http\Controllers\Dashboard::class, 'index']);
=======
// Gunakan ini agar bisa langsung melihat hasil tanpa login
Route::get('/dashboard', [App\Http\Controllers\Dashboard::class, 'index']);
//=======
// Panggil Controller yang baru kita buat biar dikenal
use App\Http\Controllers\KioskController; 

/*
|--------------------------------------------------------------------------
| Web Routes (Jalan Raya Aplikasi Épicerie)
|--------------------------------------------------------------------------
*/
>>>>>>> c805f41a3d1ab147aba1b0d8b973b51148702e1d

// 1. Halaman Utama (Katalog Produk)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// 2. Aksi Tambah ke Keranjang
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');
<<<<<<< HEAD
=======
//>>>>>>> 960f8e587b6d1a775c0d8e430c975a71ae83c361

use App\Http\Controllers\KaryawanController;

// Grouping Route Karyawan
Route::prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('index');         // List
    Route::get('/create', [KaryawanController::class, 'create'])->name('create'); // Form Tambah
    Route::post('/store', [KaryawanController::class, 'store'])->name('store');   // Aksi Simpan
    Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');  // Form Edit
    Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update'); // Aksi Update
    Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');   // Aksi Hapus
});

use App\Http\Controllers\ProdukController;

// Grouping Route Produk
Route::prefix('produk')->name('produk.')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('index');
    Route::get('/create', [ProdukController::class, 'create'])->name('create');
    Route::post('/store', [ProdukController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ProdukController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [ProdukController::class, 'update'])->name('update');
    Route::get('/hapus/{id}', [ProdukController::class, 'destroy'])->name('hapus');
});
>>>>>>> c805f41a3d1ab147aba1b0d8b973b51148702e1d
