<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfilSekolah;

class AuthController extends Controller
{
    // 1. Tampilkan Form Login
    public function showLoginForm()
    {
        $profil = ProfilSekolah::first();

        return view('auth.login', compact('profil'));
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        // 1. Validasi Input
        // Jika validasi gagal, Laravel otomatis kirim JSON error 422 
        // (karena kita sudah pasang header 'Accept: application/json' di JS tadi)
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Coba Login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // [PENTING] Kembalikan JSON, BUKAN Redirect biasa
            return response()->json([
                'status'      => 'success',
                'message'     => 'Login berhasil! Mengalihkan...',
                // Pastikan Anda punya route bernama 'dashboard' atau ganti jadi url '/dashboard'
                'redirectUrl' => url('/dashboard')
            ]);
        }

        // 3. Login Gagal
        // [PENTING] Kembalikan JSON Error
        return response()->json([
            'status'  => 'error',
            'message' => 'Username atau password salah.',
        ], 401); // 401 = Unauthorized
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }
}
