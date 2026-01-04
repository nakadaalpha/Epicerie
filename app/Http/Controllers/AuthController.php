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

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            LogAktivitas::create([
                'id_user' => Auth::id(),
                'waktu_aktivitas' => Carbon::now(),
                'jenis_aktivitas' => 'Login'
            ]);

            $role = Auth::user()->role;

            if ($role == 'Pelanggan') {
                return redirect()->route('kiosk.index');
            }

            return redirect()->route('dashboard'); 
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

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

    // --- FITUR REGISTRASI BARU (UPDATE: Tambah PIN) ---

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:user',
            'no_hp'        => 'required|numeric|unique:user',
            'pin_keamanan' => 'required|numeric|digits:6', // WAJIB 6 ANGKA
            'password'     => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nama'         => $request->nama_lengkap,
            'username'     => $request->username,
            'no_hp'        => $request->no_hp,
            'pin_keamanan' => $request->pin_keamanan, // SIMPAN PIN
            'password'     => Hash::make($request->password), 
            'role'         => 'Pelanggan', 
        ]);

        LogAktivitas::create([
            'id_user' => $user->id_user,
            'waktu_aktivitas' => Carbon::now(),
            'jenis_aktivitas' => 'Registrasi Akun Baru'
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // --- FITUR LUPA PASSWORD (UPDATE: Cek PIN) ---

    public function showForgotPasswordForm()
    {
        return view('auth.forgot');
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'username'     => 'required',
            'no_hp'        => 'required',
            'pin_keamanan' => 'required',
        ]);

        // Cek kecocokan Username + No HP + PIN
        // Jadi kalau cuma tau No HP doang, bakal gagal.
        $user = User::where('username', $request->username)
                    ->where('no_hp', $request->no_hp)
                    ->where('pin_keamanan', $request->pin_keamanan)
                    ->first();

        if ($user) {
            return view('auth.reset', compact('user'));
        }

        return back()->with('error', 'Verifikasi Gagal! Username, No HP, atau PIN salah.');
    }

    public function processResetPassword(Request $request)
    {
        $request->validate([
            'id_user'  => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($request->id_user);
        $user->password = Hash::make($request->password);
        $user->save();

        LogAktivitas::create([
            'id_user' => $user->id_user,
            'waktu_aktivitas' => Carbon::now(),
            'jenis_aktivitas' => 'Reset Password'
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login.');
    }
}