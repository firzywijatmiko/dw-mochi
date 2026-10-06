<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman form login
     */
    public function showLoginForm()
    {
        // Jika user sudah login, langsung arahkan ke dashboard sesuai role
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login'); 
    }

    /**
     * Memproses data login (Validasi Kredensial & Buat Session)
     */
    public function login(Request $request)
    {
        // Validasi input form (menggunakan username)
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek kredensial ke database menggunakan username
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            return $this->redirectBasedOnRole($user->role);
        }

        // Kembalikan pesan error jika gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Memproses logout (Hapus Session)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Berhasil keluar dari sistem.');
    }

    /**
     * Logika internal untuk mengarahkan rute sesuai role
     */
    private function redirectBasedOnRole($role)
    {
        if ($role === 'Owner' || $role === 'Admin') {
            return redirect()->intended('/owner/dashboard');
        } elseif ($role === 'Karyawan') {
            return redirect()->intended('/karyawan/dashboard');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Role tidak dikenali.');
    }
}