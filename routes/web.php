<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ProdukController;

Route::get('/', function () {
    return view('welcome');
});

// 1. Route untuk Halaman Login (Guest Only)
Route::middleware(['guest'])->group(function () {
    // Nama route 'login' ini WAJIB ada agar middleware auth tidak error
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.proses');
});

// 2. Route yang butuh Login (Dashboard)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

    // Route Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/admin', [App\Http\Controllers\Dashboard::class, 'index']);

// 1. Halaman Utama (Katalog Produk)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// 2. Aksi Tambah ke Keranjang
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');

// Grouping Route Karyawan
Route::prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('index');         // List
    Route::get('/create', [KaryawanController::class, 'create'])->name('create'); // Form Tambah
    Route::post('/store', [KaryawanController::class, 'store'])->name('store');   // Aksi Simpan
    Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');  // Form Edit
    Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update'); // Aksi Update
    Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');   // Aksi Hapus
});

// Grouping Route Produk
Route::prefix('produk')->name('produk.')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('index');
    Route::get('/create', [ProdukController::class, 'create'])->name('create');
    Route::post('/store', [ProdukController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ProdukController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [ProdukController::class, 'update'])->name('update');
    Route::get('/hapus/{id}', [ProdukController::class, 'destroy'])->name('hapus');
});
