<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. ROUTE PUBLIK / KIOSK (Halaman Depan)
// ====================================================

// Halaman Utama langsung ke Kiosk (Katalog)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// Halaman Detail Produk (Hanya 1 ini saja, yang lama diduplikat saya hapus)
Route::get('/produk/{id}', [KioskController::class, 'show'])->name('produk.show');

// Fitur Cart & Checkout
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');
// Perbaikan Typo: KiwoskController -> KioskController
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');

// Fitur Manajemen Item di Cart
Route::get('/kiosk/decrease/{id}', [KioskController::class, 'decreaseItem'])->name('kiosk.decrease');
Route::get('/kiosk/increase/{id}', [KioskController::class, 'increaseItem'])->name('kiosk.increase');
Route::get('/kiosk/remove/{id}', [KioskController::class, 'removeItem'])->name('kiosk.remove');
Route::post('/kiosk/set-qty/{id}', [KioskController::class, 'setCartQuantity'])->name('kiosk.set.qty');
Route::get('/kiosk/add-packet/{key}', [KioskController::class, 'addPacketToCart'])->name('kiosk.add.packet');

// Fitur Hold & Pending
Route::post('/kiosk/hold', [KioskController::class, 'holdOrder'])->name('kiosk.hold');
Route::get('/kiosk/pending', [KioskController::class, 'listPending'])->name('kiosk.pending');
Route::get('/kiosk/recall/{id}', [KioskController::class, 'recallOrder'])->name('kiosk.recall');


// ====================================================
// 2. ROUTE AUTHENTICATION (Login/Register/Logout)
// ====================================================

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 3. ROUTE ADMIN (Perlu Login)
// ====================================================

Route::middleware(['auth'])->group(function () {

    // Grouping URL '/admin'
    Route::prefix('admin')->group(function () {

        // --- DASHBOARD ---
        Route::get('/', [Dashboard::class, 'index'])->name('dashboard');

        // --- INVENTARIS (MANAJEMEN PRODUK) ---
        // PENTING: Saya menghapus ->name('inventaris.') di sini agar nama route
        // tetap 'produk.edit', bukan 'inventaris.produk.edit'
        Route::prefix('inventaris')->group(function () {

            // Halaman List Inventaris
            Route::get('/', [InventarisController::class, 'index'])->name('inventaris.index');

            // CRUD Produk (Nama route disesuaikan dengan View Anda)
            Route::get('/produk/create', [InventarisController::class, 'create'])->name('produk.create');
            Route::post('/produk', [InventarisController::class, 'store'])->name('produk.store');

            // INI YANG MEMPERBAIKI ERROR "Route [produk.edit] not defined"
            Route::get('/produk/{id}/edit', [InventarisController::class, 'edit'])->name('produk.edit');
            Route::put('/produk/{id}', [InventarisController::class, 'update'])->name('produk.update');
            Route::delete('/produk/{id}', [InventarisController::class, 'destroy'])->name('produk.destroy');
        });

        // --- KATEGORI ---
        Route::prefix('kategori')->name('kategori.')->group(function () {
            Route::get('/', [KategoriController::class, 'index'])->name('index');
            Route::get('/create', [KategoriController::class, 'create'])->name('create');
            Route::post('/store', [KategoriController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [KategoriController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [KategoriController::class, 'update'])->name('update');
            Route::get('/delete/{id}', [KategoriController::class, 'destroy'])->name('destroy');
        });

        // --- KARYAWAN ---
        Route::prefix('karyawan')->name('karyawan.')->group(function () {
            Route::get('/', [KaryawanController::class, 'index'])->name('index');
            Route::get('/create', [KaryawanController::class, 'create'])->name('create');
            Route::post('/store', [KaryawanController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update');
            Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');
        });

        // --- TRANSAKSI ---
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    });
});
