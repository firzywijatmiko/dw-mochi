<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // Cek apakah akun user berstatus aktif (opsional, berdasarkan field status di RAT)
        if ($user->status !== 'Aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('error', 'Akun Anda dinonaktifkan.');
        }

        // Cek apakah role user ada di dalam daftar role yang diizinkan untuk rute ini
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Jika role tidak sesuai, kembalikan error 403 (Forbidden)
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}