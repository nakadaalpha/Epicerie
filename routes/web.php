<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;


// --- ROUTE ADMIN & DASHBOARD ---
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

Route::get('/admin', [Dashboard::class, 'index']);
Route::get('/dashboard', [Dashboard::class, 'index']);

// --- ROUTE KIOSK (KASIR TABLET) ---

// 1. Halaman Utama (Katalog)
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');

// Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');
// 2. Aksi Tambah ke Keranjang
// 2. Tambah ke Keranjang
Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');

// 3. Halaman Checkout (Lihat Keranjang & Bayar)
// 3. Halaman Checkout
Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');

// 4. Proses Bayar
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');

// --- FITUR HOLD & PENDING ---
Route::post('/kiosk/hold', [KioskController::class, 'holdOrder'])->name('kiosk.hold');
Route::get('/kiosk/pending', [KioskController::class, 'listPending'])->name('kiosk.pending');
Route::get('/kiosk/recall/{id}', [KioskController::class, 'recallOrder'])->name('kiosk.recall');

// --- FITUR MANAJEMEN ITEM (Tambah/Kurang/Hapus) ---
Route::get('/kiosk/decrease/{id}', [KioskController::class, 'decreaseItem'])->name('kiosk.decrease');
Route::get('/kiosk/increase/{id}', [KioskController::class, 'increaseItem'])->name('kiosk.increase');
Route::get('/kiosk/remove/{id}', [KioskController::class, 'removeItem'])->name('kiosk.remove');
Route::post('/kiosk/set-qty/{id}', [KioskController::class, 'setCartQuantity'])->name('kiosk.set.qty');
// Tambahkan ini di bawah route 'increaseItem' atau 'setCartQuantity'
Route::get('/kiosk/add-packet/{key}', [KioskController::class, 'addPacketToCart'])->name('kiosk.add.packet');


// --- ROUTE LAINNYA ---
Route::get('/inventaris', [InventarisController::class, 'index']);

// 4. Proses Bayar (Aksi tekan tombol "Proses Transaksi")
// Pakai POST karena ngirim data form (metode pembayaran)
Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');

Route::prefix('kategori')->name('kategori.')->group(function () {
    Route::get('/', [KategoriController::class, 'index'])->name('index');
    Route::get('/create', [KategoriController::class, 'create'])->name('create');
    Route::post('/store', [KategoriController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KategoriController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KategoriController::class, 'update'])->name('update');
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
// Group Karyawan
Route::prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('index');
    Route::get('/create', [KaryawanController::class, 'create'])->name('create');
    Route::post('/store', [KaryawanController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update');
    Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');
});


// Group Produk
Route::prefix('produk')->name('produk.')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('index');
    Route::get('/create', [ProdukController::class, 'create'])->name('create');
    Route::post('/store', [ProdukController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ProdukController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [ProdukController::class, 'update'])->name('update');
    Route::get('/hapus/{id}', [ProdukController::class, 'destroy'])->name('hapus');
});