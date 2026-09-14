<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\LokasiKurirUpdated; // Load Event yang tadi kita buat

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route bawaan Laravel (biarin aja)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// =================================================================
// ROUTE UPDATE LOKASI KURIR (REALTIME)
// URL Akses: POST http://localhost:8000/api/update-lokasi
// Body (Form-Data/JSON): id_transaksi, lat, long
// =================================================================
Route::post('/update-lokasi', function (Request $request) {
    
    // 1. Validasi Data yang Masuk
    $request->validate([
        'id_transaksi' => 'required',
        'lat' => 'required',
        'long' => 'required',
    ]);

    // 2. Simpan Koordinat ke Database (Biar gak ilang pas direfresh)
    // Pastikan nama tabel 'transaksi' dan kolomnya sesuai database lu
    $affected = \Illuminate\Support\Facades\DB::table('transaksi')
        ->where('id_transaksi', $request->id_transaksi) // Cari transaksi ID ini
        ->update([
            'kurir_lat' => $request->lat,
            'kurir_long' => $request->long,
        ]);

    // 3. KIRIM SINYAL REALTIME KE PUSHER
    // Ini yang bikin peta di HP pelanggan gerak sendiri
    event(new LokasiKurirUpdated($request->id_transaksi, $request->lat, $request->long));

    return response()->json([
        'status' => 'Sukses!',
        'message' => 'Lokasi kurir berhasil diupdate & disiarkan.',
        'data' => [
            'id_transaksi' => $request->id_transaksi,
            'lat' => $request->lat,
            'long' => $request->long
        ]
    ]);
});