<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class AuthController extends Controller
{
    // --- FITUR LOGIN ---

    // 1. Menampilkan Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Proses Login (SUDAH DIPERBAIKI LOGIKANYA)
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Log Aktivitas Login
            LogAktivitas::create([
                'id_user' => Auth::id(),
                'waktu_aktivitas' => Carbon::now(),
                'jenis_aktivitas' => 'Login'
            ]);

            // === LOGIKA REDIRECT BERDASARKAN ROLE ===
            $role = Auth::user()->role;

            // A. Kalau dia Pelanggan -> Ke Web Belanja
            if ($role == 'Pelanggan') {
                return redirect()->route('kiosk.index');
            }
            else {
                return redirect()->route('admin.index');
            }

            // B. Kalau Karyawan/Admin -> Ke Dashboard Admin
            // PERBAIKAN: Jangan ke '/', tapi ke route dashboard (/admin)
            return redirect()->route('dashboard'); 
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'id_user' => Auth::id(),
                'waktu_aktivitas' => Carbon::now(),
                'jenis_aktivitas' => 'Logout'
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // --- FITUR REGISTRASI BARU ---

    // 4. Menampilkan Halaman Register
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // 5. Proses Simpan User Baru
    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:user',
            'password'     => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nama'     => $request->nama_lengkap,
            'username' => $request->username,
            'password' => Hash::make($request->password), 
            'role'     => 'Pelanggan', 
        ]);

        LogAktivitas::create([
            'id_user' => $user->id_user,
            'waktu_aktivitas' => Carbon::now(),
            'jenis_aktivitas' => 'Registrasi Akun Baru'
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
}