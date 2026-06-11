<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                // Register new user
                $user = User::create([
                    'nama' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'foto_profil' => $googleUser->getAvatar(),
                    'role' => 'kiosk', // default role
                    'email_verified_at' => now(), // Assume Google emails are verified
                    'password' => null, // No password for Google login
                ]);
            } else {
                // Update existing user with Google ID
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'foto_profil' => $user->foto_profil ?? $googleUser->getAvatar()
                ]);
            }
            
            Auth::login($user);
            
            return redirect()->intended(route('kiosk.index'))->with('success', 'Berhasil masuk dengan Google!');
            
        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }
}
