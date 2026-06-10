<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->route('login')->with('error', 'Gagal mendapatkan data email dari akun Google Anda.');
            }

            // 1. Cari berdasarkan google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Update avatar terbaru jika berubah
                $user->update([
                    'google_avatar' => $googleUser->avatar
                ]);
                
                Auth::login($user);
                return redirect()->intended(route('dashboard', absolute: false));
            }

            // 2. Cari berdasarkan email (jika belum terhubung google_id)
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Hubungkan akun dengan Google
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_avatar' => $googleUser->avatar
                ]);

                Auth::login($user);
                return redirect()->intended(route('dashboard', absolute: false));
            }

            // 3. Registrasi User Baru (Level 3 = Anggota/default)
            $newUser = User::create([
                'name'          => $googleUser->name,
                'email'         => $googleUser->email,
                'google_id'     => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
                'level_id'      => 3, // Default level untuk registrasi baru
                'is_active'     => true,
                'password'      => null // Nullable password untuk OAuth users
            ]);

            Auth::login($newUser);
            return redirect()->intended(route('dashboard', absolute: false));

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat autentikasi menggunakan Google: ' . $e->getMessage());
        }
    }
}
