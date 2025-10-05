<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => ['required'],
        ]);

        // === reCAPTCHA ===
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ])->json();

        if (empty($response['success']) || $response['success'] !== true) {
            return back()->withErrors([
                'captcha' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        // === Limit Login Attempts ===
        $key = Str::lower($request->email) . '|' . $request->ip();
        $maxAttempts = 10; // maksimal percobaan
        $decaySeconds = 60; // waktu kunci (detik)

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // Simpan waktu lockout di session agar tidak hilang saat refresh
            $request->session()->put('lockout_until', now()->addSeconds($seconds));

            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam $seconds detik."]);
        }

        // === Proses Login ===
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->forget('lockout_until');
            $request->session()->regenerate();

            $user = Auth::user();
            $firstPermission = $user->getAllPermissions()->first();

            if ($firstPermission && Route::has($firstPermission->name)) {
                return redirect()->route($firstPermission->name);
            }

            return redirect()->route('dashboard.index');
        }

        // === Gagal Login ===
        RateLimiter::hit($key, $decaySeconds);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showLoginForm(Request $request)
    {
        // Periksa apakah masih dalam masa lockout
        $lockoutUntil = $request->session()->get('lockout_until');
        $remaining = 0;

        if ($lockoutUntil && now()->lt($lockoutUntil)) {
            $remaining = now()->diffInSeconds($lockoutUntil);
        } else {
            $request->session()->forget('lockout_until');
        }

        return view('auth.login', compact('remaining'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
