<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAktivitas; // Import Model Log
use Carbon\Carbon;

class AuthController extends Controller
{
    // Menampilkan halaman login (LoginView)
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Logic: authenticate(username, password)
    public function authenticate(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Cek Autentikasi ke Database (UserModel->findByUsername & validasiPassword)
        if (Auth::attempt($credentials)) {

            // 3. SessionManager: createSession (Regenerate ID untuk keamanan)
            $request->session()->regenerate();

            // 4. LogAktivitasModel: record()
            LogAktivitas::create([
                'id_user' => Auth::id(), // Ambil ID user yang sedang login
                'waktu_aktivitas' => Carbon::now(),
                'jenis_aktivitas' => 'Login'
            ]);

            // Redirect ke Dashboard
            return redirect()->intended('dashboard');
        }

        // Jika Gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    // Logic: logoutUser(id_user)
    public function logout(Request $request)
    {
        // Catat Log Logout sebelum menghapus sesi
        if (Auth::check()) {
            LogAktivitas::create([
                'id_user' => Auth::id(),
                'waktu_aktivitas' => Carbon::now(),
                'jenis_aktivitas' => 'Logout'
            ]);
        }

        // 1. SessionManager: destroySession
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect kembali ke halaman login
        return redirect('/login');
    }
}
