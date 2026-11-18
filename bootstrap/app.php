<?php

use App\Http\Middleware\HanyaAdmin;
use App\Http\Middleware\HanyaSopir;
use App\Http\Middleware\PastikanPenggunaAktif;
use App\Http\Middleware\WajibGantiPassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'aktif' => PastikanPenggunaAktif::class,
            'ganti.password' => WajibGantiPassword::class,
            'admin' => HanyaAdmin::class,
            'sopir' => HanyaSopir::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);

        // Pengguna yang sudah login dan membuka /login diarahkan sesuai perannya.
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->hasRole('sopir') ? route('portal.beranda') : route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sesi/CSRF habis: jangan tampilkan halaman 419 mentah (MODUL-SOPIR-GPS 4.2).
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi Anda telah berakhir. Silakan masuk kembali.'], 419);
            }

            return redirect()->route('login')->withErrors([
                'username' => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
            ]);
        });
    })->create();
