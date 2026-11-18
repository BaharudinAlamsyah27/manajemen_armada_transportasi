<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $attempted = Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ]) || Auth::attempt([
            'email' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        if ($attempted) {
            $user = Auth::user();
            if (!$user->aktif || ($user->karyawan && !$user->karyawan->aktif)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan.',
                ]);
            }

            $request->session()->regenerate();

            if ($user->hasRole('sopir')) {
                return redirect()->intended(route('portal.beranda'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Kredensial tidak cocok dengan data kami.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
