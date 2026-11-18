<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rute panel admin. Sopir diarahkan ke portal (403 untuk permintaan JSON).
 */
class HanyaAdmin
{
    public const PERAN_ADMIN = ['admin_pusat', 'admin_cabang', 'keuangan'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('sopir')) {
            if ($request->expectsJson()) {
                abort(403, 'Akses khusus admin.');
            }

            return redirect()->route('portal.beranda');
        }

        if (!$user || !$user->hasAnyRole(self::PERAN_ADMIN)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
