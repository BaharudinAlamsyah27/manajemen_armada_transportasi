<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rute portal sopir: hanya peran sopir yang tertaut ke data karyawan.
 */
class HanyaSopir
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('sopir')) {
            return redirect()->route('dashboard');
        }

        if (!$user->karyawan_id) {
            abort(403, 'Akun sopir belum ditautkan ke data karyawan. Hubungi admin.');
        }

        return $next($request);
    }
}
