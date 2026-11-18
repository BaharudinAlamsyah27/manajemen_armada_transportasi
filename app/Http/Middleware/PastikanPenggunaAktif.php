<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengguna nonaktif (atau karyawan tertaut yang nonaktif) langsung ditolak
 * pada permintaan berikutnya, meskipun sesinya masih berjalan.
 */
class PastikanPenggunaAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (!$user->aktif || ($user->karyawan_id && !$user->karyawan?->aktif))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Akun Anda sedang dinonaktifkan. Hubungi admin.',
            ]);
        }

        return $next($request);
    }
}
