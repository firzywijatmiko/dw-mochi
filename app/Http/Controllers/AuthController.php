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
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek kredensial ke database menggunakan username
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Cek apakah status user nonaktif (diblokir)
            if (isset($user->status) && strtolower($user->status) === 'nonaktif') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda sudah nonaktif dan tidak dapat mengakses sistem.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();
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
            return redirect()->intended('/employee/dashboard'); 
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Role tidak dikenali.');
    }
}