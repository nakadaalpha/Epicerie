<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;

Route::get('/', function () {
    return view('welcome');
});

// 1. Route untuk Halaman Login (Guest Only)
Route::middleware(['guest'])->group(function () {
    // Nama route 'login' ini WAJIB ada agar middleware auth tidak error
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.proses');
});

Route::get('/admin', [App\Http\Controllers\Dashboard::class, 'index'])->name('dashboard');

// 1. Halaman Utama (Katalog Produk)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');
// 2. Aksi Tambah ke Keranjang
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
// Pakai POST karena ngirim data form (metode pembayaran)
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');

Route::prefix('kategori')->name('kategori.')->group(function () {
    Route::get('/', [KategoriController::class, 'index'])->name('index');
    Route::get('/create', [KategoriController::class, 'create'])->name('create');
    Route::post('/store', [KategoriController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KategoriController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [KategoriController::class, 'update'])->name('update');
    Route::get('/delete/{id}', [KategoriController::class, 'destroy'])->name('destroy');
});


// --- INVENTARIS (MANAJEMEN PRODUK) --- // 

// 1. READ (Index)
Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris'); // Nama route ini tetap biar navbar tidak error

// 2. CREATE
Route::get('/produk/create', [InventarisController::class, 'create'])->name('produk.create');
Route::post('/produk', [InventarisController::class, 'store'])->name('produk.store');

// 3. EDIT & UPDATE
Route::get('/produk/{id}/edit', [InventarisController::class, 'edit'])->name('produk.edit');
Route::put('/produk/{id}', [InventarisController::class, 'update'])->name('produk.update');

// 4. DELETE
Route::delete('/produk/{id}', [InventarisController::class, 'destroy'])->name('produk.destroy');

// --- TRANSAKSI --- // 

//Transaksi
Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');

// Grouping Route Karyawan
Route::prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('index');         // List
    Route::get('/create', [KaryawanController::class, 'create'])->name('create'); // Form Tambah
    Route::post('/store', [KaryawanController::class, 'store'])->name('store');   // Aksi Simpan
    Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');  // Form Edit
    Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update'); // Aksi Update
    Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');   // Aksi Hapus
});
