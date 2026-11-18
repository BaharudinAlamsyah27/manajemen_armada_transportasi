<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GantiPasswordController extends Controller
{
    public function edit(Request $request)
    {
        $layout = $request->user()->hasRole('sopir') ? 'layouts.portal' : 'layouts.app';

        return view('auth.ganti-password', compact('layout'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_lama', Password::min(8)],
        ], [], [
            'password_lama' => 'kata sandi lama',
            'password' => 'kata sandi baru',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->input('password')),
            'wajib_ganti_password' => false,
        ]);

        $tujuan = $user->hasRole('sopir') ? route('portal.beranda') : route('dashboard');

        return redirect($tujuan)->with('success', 'Kata sandi berhasil diganti.');
    }
}
