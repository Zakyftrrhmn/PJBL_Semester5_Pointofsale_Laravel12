<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Tentukan kunci throttling.
     */
    protected function throttleKey(Request $request): string
    {
        // Kunci dibuat dari input email saat ini (POST request)
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }

    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required'],
        ]);

        $key = $this->throttleKey($request);
        $maxAttempts = 10;
        $decaySeconds = 60; // 1 menit

        // 2. Periksa Lockout (Throttle)
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // Jika form berhasil di-submit saat sedang lockout, tolak dengan exception
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam $seconds detik.",
            ]);
        }

        // 3. Verifikasi reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ])->json();

        if (empty($response['success']) || $response['success'] !== true) {
            // Hit rate limiter jika CAPTCHA gagal verifikasi
            RateLimiter::hit($key, $decaySeconds);
            // Simpan email ke session saat HITTING limiter
            $request->session()->put('locked_email', $request->input('email'));

            return back()->withErrors([
                'captcha_error' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        // 4. Coba Autentikasi
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($key); // Hapus lock pada sukses login
            $request->session()->forget('locked_email'); // Hapus locked_email setelah sukses
            $request->session()->regenerate();

            // Logika Redirect
            $user = Auth::user();
            $firstPermission = $user->getAllPermissions()->first();

            if ($firstPermission && Route::has($firstPermission->name)) {
                return redirect()->route($firstPermission->name);
            }

            return redirect()->route('dashboard.index');
        }

        // 5. Autentikasi Gagal
        RateLimiter::hit($key, $decaySeconds);
        // Simpan email ke session saat HITTING limiter
        $request->session()->put('locked_email', $request->input('email'));

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showLoginForm(Request $request)
    {
        // Jika sudah login, redirect
        if (Auth::check()) {
            $user = Auth::user();
            $firstPermission = $user->getAllPermissions()->first();

            if ($firstPermission && Route::has($firstPermission->name)) {
                return redirect()->route($firstPermission->name);
            }
            return redirect()->route('admin');
        }

        // Cek lockout status
        $maxAttempts = 10;

        // Ambil email yang terakhir kali gagal (tersimpan di session)
        $email_for_key = $request->session()->get('locked_email');

        $remaining = 0;

        if ($email_for_key) {
            // Buat key menggunakan email yang disimpan di session
            $key = Str::lower($email_for_key) . '|' . $request->ip();

            // Hitung sisa waktu lockout
            $remaining = RateLimiter::tooManyAttempts($key, $maxAttempts)
                ? RateLimiter::availableIn($key)
                : 0;

            // Jika lockout sudah berakhir, hapus session locked_email
            if ($remaining === 0) {
                $request->session()->forget('locked_email');
            }
        }

        // Header Cache-Control
        return response(view('auth.login', compact('remaining')))->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sun, 01 Jan 1990 00:00:00 GMT',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
