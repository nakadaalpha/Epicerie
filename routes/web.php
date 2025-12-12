<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard;

Route::get('/', function () {
    return view('welcome');
});

// Gunakan ini agar bisa langsung melihat hasil tanpa login
Route::get('/dashboard', [App\Http\Controllers\Dashboard::class, 'index']);
