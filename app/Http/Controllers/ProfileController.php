<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{

    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user()
        ]);
    }

    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Input
        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Validasi Password (Opsional, hanya jika diisi)
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        // 2. Update Data Dasar
        $user->username = $request->username;

        // 3. Cek apakah user ingin ganti password?
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        // 4. Simpan ke Database
        $user->save(); // Pastikan variabel $user adalah instance model User

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
