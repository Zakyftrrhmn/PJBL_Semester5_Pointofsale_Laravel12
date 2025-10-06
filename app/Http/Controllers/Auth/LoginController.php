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

        $key = Str::lower($request->email) . '|' . $request->ip();
        $maxAttempts = 10;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            $request->session()->put('lockout_until', now()->addSeconds($seconds));

            return back()
                ->withErrors(['emaizl' => "Terlalu banyak percobaan. Coba lagi dalam $seconds detik."]);
        }

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

        RateLimiter::hit($key, $decaySeconds);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $firstPermission = $user->getAllPermissions()->first();

            if ($firstPermission && Route::has($firstPermission->name)) {
                return redirect()->route($firstPermission->name);
            }

            return redirect('/admin');
        }

        $lockoutUntil = $request->session()->get('lockout_until');
        $remaining = 0;

        if ($lockoutUntil && now()->lt($lockoutUntil)) {
            $remaining = now()->diffInSeconds($lockoutUntil);
        } else {
            $request->session()->forget('lockout_until');
        }

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
