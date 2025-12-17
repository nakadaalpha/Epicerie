<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LogAktivitas; // Pastikan model ini ada
use Carbon\Carbon;

class AuthController extends Controller
{
    // --- FITUR LOGIN ---

    // 1. Menampilkan Halaman Login (Perbaikan Error "Undefined method")
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Proses Login
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

            return redirect()->intended('login');
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
    // ... method register ...
    public function register(Request $request)
    {
        // Validasi Input
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            // Perbaikan: Gunakan 'unique:user' (nama tabel singular)
            'username'     => 'required|string|max:255|unique:user',
            'password'     => 'required|string|min:6|confirmed',
        ]);

        // Buat User
        $user = User::create([
            // Perbaikan: Mapping ke kolom database 'nama'
            'nama'     => $request->nama_lengkap,
            'username' => $request->username,
            'password' => Hash::make($request->password), // Enkripsi password
            'role'     => 'karyawan',
        ]);

        // Log Register
        LogAktivitas::create([
            'id_user' => $user->id_user, // Perbaikan: Ambil ID dari 'id_user'
            'waktu_aktivitas' => Carbon::now(),
            'jenis_aktivitas' => 'Registrasi Akun Baru'
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
}
