<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kata sandi awal / hasil reset wajib diganti sebelum memakai aplikasi.
 */
class WajibGantiPassword
{
    private const RUTE_DIIZINKAN = ['password.ganti', 'password.ganti.simpan', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->wajib_ganti_password && !in_array($request->route()?->getName(), self::RUTE_DIIZINKAN, true)) {
            return redirect()->route('password.ganti')
                ->with('success', 'Silakan ganti kata sandi awal Anda sebelum melanjutkan.');
        }

        return $next($request);
    }
}
