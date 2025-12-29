<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek Login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek Role: Bolehin 'Karyawan' DAN 'Pemilik'
        $userRole = Auth::user()->role;
        
        if ($userRole !== 'Karyawan' && $userRole !== 'Pemilik') {
            // Kalau dia Pelanggan, tendang ke toko
            return redirect()->route('kiosk.index'); 
        }

        return $next($request);
    }
}