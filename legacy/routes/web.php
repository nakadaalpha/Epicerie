<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

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
    
    // Google OAuth Routes
    Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
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
    Route::post('/add-to-cart/{id}', [CartController::class, 'addToCart'])->name('kiosk.add');
    Route::get('/keranjang', [CartController::class, 'cart'])->name('kiosk.cart');
    Route::get('/checkout', [CheckoutController::class, 'checkoutPage'])->name('kiosk.checkout');

    // --- Manajemen Item ---
    Route::get('/kiosk/remove/{id}', [CartController::class, 'removeItem'])->name('kiosk.remove');
    Route::get('/kiosk/increase/{id}', [CartController::class, 'increaseItem'])->name('kiosk.increase');
    Route::get('/kiosk/decrease/{id}', [CartController::class, 'decreaseItem'])->name('kiosk.decrease');
    Route::get('/kiosk/empty-cart', [CartController::class, 'emptyCart'])->name('kiosk.empty');

    // --- Pembayaran & Transaksi ---
    Route::post('/pay', [CheckoutController::class, 'processPayment'])->name('kiosk.pay');
    Route::post('/midtrans-success', [CheckoutController::class, 'midtransSuccess'])->name('kiosk.midtrans.success');
    Route::get('/kiosk/success/{id}', [CheckoutController::class, 'successPage'])->name('kiosk.success');
    Route::post('/transaksi/{id}/selesai', [CheckoutController::class, 'completeTransaction'])->name('kiosk.complete');

    // --- FITUR ULASAN ---
    Route::post('/review/store', [ReviewController::class, 'storeReview'])->name('review.store');

    // --- User Dashboard & Profile ---
    Route::get('/profile', [ProfileController::class, 'profile'])->name('kiosk.profile');
    Route::get('/riwayat', [ProfileController::class, 'riwayatTransaksi'])->name('kiosk.riwayat');
    Route::get('/tracking/{id}', [ProfileController::class, 'trackingPage'])->name('kiosk.tracking');
    Route::get('/ulasan', [ProfileController::class, 'ulasanPage'])->name('kiosk.ulasan');

    // --- FITUR UPDATE PROFIL ---
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/download-qr', [ProfileController::class, 'downloadQr'])->name('profile.download-qr');
    Route::post('/profile/request-card', [ProfileController::class, 'requestCetakKartu'])->name('profile.request.card');

    // --- FITUR VERIFIKASI (EMAIL & OTP) ---
    Route::post('/email/verify-manual', [VerificationController::class, 'sendEmailVerification'])->name('verifikasi.manual');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verifyHandler'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/phone/request-otp', [VerificationController::class, 'requestOtp'])->name('phone.requestOtp');
    Route::post('/phone/verify-otp', [VerificationController::class, 'verifyOtp'])->name('phone.verifyOtp');

    // --- FITUR ALAMAT ---
    Route::post('/profile/address', [ProfileController::class, 'addAddress'])->name('profile.address.add');
    Route::post('/profile/address/update/{id}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/address/delete/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.address.delete');
    Route::post('/profile/address/set-primary/{id}', [ProfileController::class, 'setPrimaryAddress'])->name('address.setPrimary');

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
    // Menggunakan parameter kustom agar sesuai dengan controller lama yang memakai {id}
    Route::resource('kategori', KategoriController::class)->parameters([
        'kategori' => 'id',
    ])->except(['show']);

    // --- SLIDER ---
    // Menggunakan parameter kustom agar sesuai dengan controller lama
    Route::resource('slider', SliderController::class)->parameters([
        'slider' => 'id',
    ])->except(['show']);

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
