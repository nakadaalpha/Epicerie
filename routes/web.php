<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KurirController; // <-- TAMBAHAN BARU
use App\Http\Middleware\AdminOnly; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. ROUTE GUEST (Hanya yang BELUM Login)
// ====================================================
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.proses');
});

// Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// ====================================================
// 2. ROUTE PUBLIK (Bisa Diakses Siapa Saja)
// ====================================================
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');
Route::get('/produk/{id}', [KioskController::class, 'show'])->name('produk.show');


// ====================================================
// 3. ROUTE PELANGGAN / BELANJA (WAJIB LOGIN)
// ====================================================
Route::middleware(['auth'])->group(function () {
    
    // --- Keranjang & Checkout ---
    Route::get('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');
    Route::get('/checkout', [KioskController::class, 'checkout'])->name('kiosk.checkout');
    
    // --- Manajemen Item Keranjang ---
    Route::get('/kiosk/remove/{id}', [KioskController::class, 'removeItem'])->name('kiosk.remove');
    Route::get('/kiosk/increase/{id}', [KioskController::class, 'increaseItem'])->name('kiosk.increase');
    Route::get('/kiosk/decrease/{id}', [KioskController::class, 'decreaseItem'])->name('kiosk.decrease');
    Route::get('/kiosk/empty-cart', [KioskController::class, 'emptyCart'])->name('kiosk.empty');
    
    // --- Pembayaran ---
    Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');
    Route::post('/midtrans-success', [KioskController::class, 'midtransSuccess'])->name('kiosk.midtrans.success');
    Route::get('/kiosk/success/{id}', [KioskController::class, 'successPage'])->name('kiosk.success');

    // --- User Dashboard & Profile ---
    Route::get('/profile', [KioskController::class, 'profile'])->name('kiosk.profile');
    Route::get('/riwayat', [KioskController::class, 'riwayatTransaksi'])->name('kiosk.riwayat');
    
    // HALAMAN TRACKING REALTIME
    Route::get('/tracking/{id}', [KioskController::class, 'trackingPage'])->name('kiosk.tracking');

    // --- FITUR UPDATE PROFIL (BARU DITAMBAHKAN) ---
    Route::post('/profile/update', [KioskController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [KioskController::class, 'updatePhoto'])->name('profile.photo');
    
    // --- FITUR ALAMAT (BARU DITAMBAHKAN) ---
    Route::post('/profile/address', [KioskController::class, 'addAddress'])->name('profile.address.add');
    // Rute untuk Update Alamat (PENTING: Tambahkan ini agar tombol Edit jalan)
    Route::post('/profile/address/update/{id}', [App\Http\Controllers\KioskController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/address/delete/{id}', [KioskController::class, 'deleteAddress'])->name('profile.address.delete');

    // --- Placeholder Routes (Bundling, Pending Order, dll) ---
    Route::post('/kiosk/set-qty/{id}', [KioskController::class, 'setCartQuantity'])->name('kiosk.set.qty');
    Route::get('/kiosk/add-packet/{key}', [KioskController::class, 'addPacketToCart'])->name('kiosk.add.packet');
    Route::post('/kiosk/hold', [KioskController::class, 'holdOrder'])->name('kiosk.hold');
    Route::get('/kiosk/pending', [KioskController::class, 'listPending'])->name('kiosk.pending');
    Route::get('/kiosk/recall/{id}', [KioskController::class, 'recallOrder'])->name('kiosk.recall');

    // --- ROUTE KHUSUS KURIR (APLIKASI KURIR) ---
    Route::get('/kurir/dashboard', [KurirController::class, 'index'])->name('kurir.index');
    Route::post('/kurir/mulai/{id}', [KurirController::class, 'mulaiAntar'])->name('kurir.mulai');
    Route::post('/kurir/selesai/{id}', [KurirController::class, 'selesaiAntar'])->name('kurir.selesai');
});


// ====================================================
// 4. ROUTE ADMIN / KARYAWAN (DASHBOARD)
// ====================================================
Route::middleware(['auth', AdminOnly::class])->prefix('admin')->group(function () {
    
    Route::get('/', [Dashboard::class, 'index'])->name('dashboard'); // Akses: /admin

    Route::prefix('inventaris')->group(function () {
        Route::get('/', [InventarisController::class, 'index'])->name('inventaris.index');
        Route::get('/produk/create', [InventarisController::class, 'create'])->name('produk.create');
        Route::post('/produk', [InventarisController::class, 'store'])->name('produk.store');
        Route::get('/produk/{id}/edit', [InventarisController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{id}', [InventarisController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{id}', [InventarisController::class, 'destroy'])->name('produk.destroy');
    });

    Route::prefix('kategori')->name('kategori.')->group(function () {
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::get('/create', [KategoriController::class, 'create'])->name('create');
        Route::post('/store', [KategoriController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [KategoriController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [KategoriController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/', [KaryawanController::class, 'index'])->name('index');
        Route::get('/create', [KaryawanController::class, 'create'])->name('create');
        Route::post('/store', [KaryawanController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [KaryawanController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update');
        Route::get('/hapus/{id}', [KaryawanController::class, 'destroy'])->name('hapus');
    });

    // --- MANAJEMEN TRANSAKSI (OPERASIONAL) ---
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi/update-status/{id}', [TransaksiController::class, 'updateStatus'])->name('transaksi.update');

    // --- LAPORAN PENJUALAN (ANALYTICS) ---
    Route::get('/laporan', [TransaksiController::class, 'laporan'])->name('laporan.index');
});