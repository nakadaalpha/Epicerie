<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\EmailVerificationRequest; // Import ini

class VerificationController extends Controller
{
    // 1. MENGIRIM LINK VERIFIKASI (TOMBOL DI PROFIL)
    public function sendEmailVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('success', 'Email sudah terverifikasi sebelumnya.');
        }

        // Kirim notifikasi email
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Link verifikasi telah dikirim ke email Anda! Cek Inbox/Spam.');
    }

    // 2. MENANGANANI KLIK LINK DARI EMAIL (Route baru)
    public function verifyHandler(EmailVerificationRequest $request)
    {
        $request->fulfill(); // Tandai email sebagai verified di database

        return redirect()->route('kiosk.profile')->with('success', 'Selamat! Email berhasil diverifikasi.');
    }

    // 3. LOGIKA OTP NOMOR HP
    public function requestOtp()
    {
        $user = Auth::user();

        if ($user->no_hp_verified_at) {
            return response()->json(['status' => 'error', 'message' => 'Nomor HP sudah terverifikasi!']);
        }

        if (!$user->no_hp) {
            return response()->json(['status' => 'error', 'message' => 'Harap lengkapi nomor HP di profil dulu.']);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $key = 'otp_hp_' . $user->id_user;
        Cache::put($key, $otp, now()->addMinutes(5));

        // LOGIKA KIRIM WHATSAPP (Contoh menggunakan Fonnte)
        $token = "YOUR_FONNTE_TOKEN"; // Ganti dengan token API Anda
        $message = "Halo {$user->nama},\n\nKode OTP verifikasi akun Épicerie Anda adalah: *{$otp}*.\n\nKode ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun.";

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/send', [
            'target' => $user->no_hp,
            'message' => $message,
            'countryCode' => '62', // Opsional
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'message' => 'OTP berhasil dikirim ke WhatsApp Anda!'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim WhatsApp. Silakan coba lagi nanti.',
                'detail' => $response->body() // Hanya untuk debug
            ]);
        }
    }
    // public function requestOtp()
    // {
    //     $user = Auth::user();

    //     if ($user->no_hp_verified_at) {
    //         return response()->json(['status' => 'error', 'message' => 'Nomor HP sudah terverifikasi!']);
    //     }

    //     if (!$user->no_hp) {
    //         return response()->json(['status' => 'error', 'message' => 'Harap lengkapi nomor HP di profil dulu.']);
    //     }

    //     $otp = rand(100000, 999999);
    //     $key = 'otp_hp_' . $user->id_user;
    //     Cache::put($key, $otp, now()->addMinutes(5));

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'OTP Terkirim!',
    //         'debug_otp' => $otp
    //     ]);
    // }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);

        $user = Auth::user();
        $key = 'otp_hp_' . $user->id_user;
        $cachedOtp = Cache::get($key);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah atau kadaluarsa.']);
        }

        $user->no_hp_verified_at = now();
        $user->save();
        Cache::forget($key);

        return response()->json(['status' => 'success', 'message' => 'Nomor HP berhasil diverifikasi!']);
    }
}
