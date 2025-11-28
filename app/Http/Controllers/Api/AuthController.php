<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input email dan password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Coba otentikasi
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Jika gagal, lempar exception dengan pesan error
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        $user = $request->user();

        // Membuat token Sanctum. 'mobile' adalah nama token.
        // Nama token bisa disesuaikan, misal 'pos-app'
        $token = $user->createToken('mobile')->plainTextToken;

        // Mengembalikan token dan data user dalam format JSON
        return response()->json([
            'message' => 'Login berhasil!',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    /**
     * Logout user dengan menghapus token saat ini.
     */
    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan (logout)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil'], 200);
    }

    /**
     * Mengambil data user yang sedang login (membutuhkan otentikasi token).
     */
    public function user(Request $request)
    {
        // Mengambil data user yang sedang login
        return response()->json(['user' => $request->user()]);
    }
}
