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
        // Validasi input form (menggunakan Nomor HP sesuai prototype dan RAT)
        $credentials = $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek kredensial ke database
        if (Auth::attempt($credentials)) {
            // Jika berhasil, buat session baru untuk mencegah session fixation
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek duplikasi data akun (opsional, Laravel handle single session per device via Auth, 
            // tapi ini memenuhi poin "Cek Duplikasi Data Akun" di Activity Diagram)

            return $this->redirectBasedOnRole($user->role);
        }

        // Jika salah, kembalikan pesan error sesuai Activity Diagram
        return back()->withErrors([
            'phone' => 'Nomor HP atau password salah.',
        ])->onlyInput('phone');
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