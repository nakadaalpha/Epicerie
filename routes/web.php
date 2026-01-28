<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KurirController;
use App\Http\Middleware\AdminOnly;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\CardSettingController;

// ====================================================
// 1. ROUTE GUEST (Hanya yang BELUM Login)
// ====================================================
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.proses');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'verifyUser'])->name('password.verify');
    Route::post('/reset-password', [AuthController::class, 'processResetPassword'])->name('password.reset.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ====================================================
// 2. ROUTE PUBLIK
// ====================================================
Route::get('/', [KioskController::class, 'index'])->name('kiosk.index');
Route::get('/produk/{id}', [KioskController::class, 'show'])->name('produk.show');
Route::get('/kiosk/search', [KioskController::class, 'search'])->name('kiosk.search');

// ====================================================
// 3. ROUTE PELANGGAN / BELANJA (WAJIB LOGIN)
// ====================================================
Route::middleware(['auth'])->group(function () {

    // --- Keranjang & Checkout ---
    Route::post('/add-to-cart/{id}', [KioskController::class, 'addToCart'])->name('kiosk.add');
    Route::get('/keranjang', [KioskController::class, 'cart'])->name('kiosk.cart');
    Route::get('/checkout', [KioskController::class, 'checkoutPage'])->name('kiosk.checkout');

    // --- Manajemen Item ---
    Route::get('/kiosk/remove/{id}', [KioskController::class, 'removeItem'])->name('kiosk.remove');
    Route::get('/kiosk/increase/{id}', [KioskController::class, 'increaseItem'])->name('kiosk.increase');
    Route::get('/kiosk/decrease/{id}', [KioskController::class, 'decreaseItem'])->name('kiosk.decrease');
    Route::get('/kiosk/empty-cart', [KioskController::class, 'emptyCart'])->name('kiosk.empty');
    Route::post('/kiosk/set-qty/{id}', [KioskController::class, 'setCartQuantity'])->name('kiosk.set.qty');

    // --- Pembayaran & Transaksi ---
    Route::post('/pay', [KioskController::class, 'processPayment'])->name('kiosk.pay');
    Route::post('/midtrans-success', [KioskController::class, 'midtransSuccess'])->name('kiosk.midtrans.success');
    Route::get('/kiosk/success/{id}', [KioskController::class, 'successPage'])->name('kiosk.success');
    Route::post('/transaksi/{id}/selesai', [KioskController::class, 'completeTransaction'])->name('kiosk.complete');

    // --- FITUR ULASAN (BARU DITAMBAHKAN) ---
    Route::post('/review/store', [KioskController::class, 'storeReview'])->name('review.store');

    // --- User Dashboard & Profile ---
    Route::get('/profile', [KioskController::class, 'profile'])->name('kiosk.profile');
    Route::get('/riwayat', [KioskController::class, 'riwayatTransaksi'])->name('kiosk.riwayat');
    Route::get('/tracking/{id}', [KioskController::class, 'trackingPage'])->name('kiosk.tracking');
    Route::get('/ulasan', [KioskController::class, 'ulasanPage'])->name('kiosk.ulasan');

    // --- FITUR UPDATE PROFIL ---
    Route::post('/profile/photo', [KioskController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/update', [KioskController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/download-qr', [KioskController::class, 'downloadQr'])->name('profile.download-qr');
    Route::post('/profile/request-card', [KioskController::class, 'requestCetakKartu'])->name('profile.request.card');

    // --- FITUR VERIFIKASI (EMAIL & OTP) ---
    Route::post('/email/verify-manual', [VerificationController::class, 'sendEmailVerification'])->name('verifikasi.manual');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verifyHandler'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/phone/request-otp', [VerificationController::class, 'requestOtp'])->name('phone.requestOtp');
    Route::post('/phone/verify-otp', [VerificationController::class, 'verifyOtp'])->name('phone.verifyOtp');

    // --- FITUR ALAMAT ---
    Route::post('/profile/address', [KioskController::class, 'addAddress'])->name('profile.address.add');
    Route::post('/profile/address/update/{id}', [KioskController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/address/delete/{id}', [KioskController::class, 'deleteAddress'])->name('profile.address.delete');
    Route::post('/profile/address/set-primary/{id}', [KioskController::class, 'setPrimaryAddress'])->name('address.setPrimary');

    // --- ROUTE KHUSUS KURIR ---
    Route::get('/kurir/dashboard', [KurirController::class, 'index'])->name('kurir.index');
    Route::post('/kurir/mulai/{id}', [KurirController::class, 'mulaiAntar'])->name('kurir.mulai');
    Route::post('/kurir/selesai/{id}', [KurirController::class, 'selesaiAntar'])->name('kurir.selesai');
    Route::get('/kurir/transaksi/detail/{id}', [KurirController::class, 'getDetailTransaksi']);
    Route::post('/kurir/update-lokasi', [KurirController::class, 'updateLokasi'])->name('kurir.update_lokasi');
});

// ====================================================
// 4. ROUTE ADMIN (DASHBOARD)
// ====================================================
Route::middleware(['auth', AdminOnly::class])->prefix('admin')->group(function () {

    Route::get('/', [Dashboard::class, 'index'])->name('dashboard');

    // --- INVENTARIS ---
    Route::prefix('inventaris')->group(function () {
        Route::get('/', [InventarisController::class, 'index'])->name('inventaris.index');
        Route::get('/produk/create', [InventarisController::class, 'create'])->name('produk.create');
        Route::post('/produk', [InventarisController::class, 'store'])->name('produk.store');
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
        Route::delete('/delete/{id}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    // --- SLIDER ---
    Route::prefix('slider')->name('slider.')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('index');
        Route::get('/create', [SliderController::class, 'create'])->name('create');
        Route::post('/store', [SliderController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [SliderController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [SliderController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [SliderController::class, 'destroy'])->name('destroy');
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

    // --- TRANSAKSI & KASIR ---
    Route::controller(TransaksiController::class)->group(function () {
        Route::get('/laporan', 'laporan')->name('laporan.index');
        Route::get('/transaksi/baru', 'create')->name('transaksi.create');
        Route::post('/transaksi/store', 'store')->name('transaksi.store');
        Route::get('/transaksi', 'index')->name('transaksi.index');
        Route::post('/transaksi/update-status/{id}', 'updateStatus')->name('transaksi.update');
        Route::get('/transaksi/{id}', 'show')->name('transaksi.show');
        Route::delete('/transaksi/{id}', 'destroy')->name('transaksi.destroy');
        Route::get('/transaksi/{id}/print', 'print')->name('transaksi.print');
    });

    // --- MANAJEMEN KARTU (FITUR BARU) ---
    Route::controller(CardSettingController::class)->prefix('member-card')->name('card.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/settings', 'settings')->name('settings');
        Route::post('/settings', 'updateSettings')->name('update');
        Route::get('/print/{id}', 'printPdf')->name('print');
        Route::post('/complete/{id}', 'markAsComplete')->name('complete');
    });
});
