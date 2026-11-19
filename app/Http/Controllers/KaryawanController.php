<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\User;
use App\Services\NomorDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with(['cabang', 'user']);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            });
        }
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->input('jabatan'));
        }
        if ($request->filled('cabang_id') && Auth::user()->hasAnyRole(['admin_pusat', 'keuangan'])) {
            $query->where('cabang_id', $request->input('cabang_id'));
        }

        $karyawans = $query->orderBy('kode')->get();
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();

        return view('karyawan.index', compact('karyawans', 'cabangs'));
    }

    public function create()
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        return view('karyawan.form', ['karyawan' => new Karyawan(), 'cabangs' => $cabangs]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'cabang_id' => $user->hasRole('admin_pusat') ? 'required|exists:cabangs,id' : 'nullable|exists:cabangs,id',
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|in:sopir,kernet,mekanik,staf_gudang,staf_admin',
            'no_hp' => 'nullable|string|max:30',
            'no_sim' => 'nullable|string|max:50',
            'jenis_sim' => 'nullable|in:C,A,B1,B2',
            'sim_berlaku_sampai' => 'nullable|date',
            'aktif' => 'nullable|boolean',
        ]);

        $cabangId = $user->hasRole('admin_pusat') ? (int) $validated['cabang_id'] : (int) $user->cabang_id;
        $validated['cabang_id'] = $cabangId;
        $validated['kode'] = NomorDokumenService::buat($cabangId, 'KRY');
        $validated['aktif'] = $request->boolean('aktif', true);

        Karyawan::create($validated);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        return view('karyawan.form', compact('karyawan', 'cabangs'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|in:sopir,kernet,mekanik,staf_gudang,staf_admin',
            'no_hp' => 'nullable|string|max:30',
            'no_sim' => 'nullable|string|max:50',
            'jenis_sim' => 'nullable|in:C,A,B1,B2',
            'sim_berlaku_sampai' => 'nullable|date',
            'aktif' => 'nullable|boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $karyawan->update($validated);

        if (!$validated['aktif'] && $karyawan->user) {
            $karyawan->user->update(['aktif' => false]);
        }

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        if ($karyawan->pengirimans()->exists()) {
            $karyawan->update(['aktif' => false]);
            $karyawan->user?->update(['aktif' => false]);
            return redirect()->route('karyawan.index')->with('success', 'Karyawan memiliki riwayat pengiriman, status diubah menjadi nonaktif.');
        }

        $karyawan->user?->update(['aktif' => false]);
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    /**
     * Buat akun login portal untuk karyawan berjabatan sopir (MODUL-SOPIR-GPS 2).
     * Kata sandi awal acak, ditampilkan sekali, wajib diganti saat login pertama.
     */
    public function buatAkun(Request $request, Karyawan $karyawan)
    {
        if ($karyawan->jabatan !== 'sopir') {
            return back()->withErrors(['akun' => 'Akun portal hanya untuk karyawan berjabatan sopir.']);
        }
        if (!$karyawan->aktif) {
            return back()->withErrors(['akun' => 'Karyawan nonaktif tidak dapat dibuatkan akun.']);
        }
        if ($karyawan->user) {
            return back()->withErrors(['akun' => 'Karyawan ini sudah memiliki akun.']);
        }

        $validated = $request->validate([
            'username' => 'required|string|min:4|max:30|alpha_dash|unique:users,username',
        ]);

        $passwordAwal = Str::password(10, symbols: false);

        $user = User::create([
            'name' => $karyawan->nama,
            'username' => strtolower($validated['username']),
            'password' => Hash::make($passwordAwal),
            'cabang_id' => $karyawan->cabang_id,
            'karyawan_id' => $karyawan->id,
            'wajib_ganti_password' => true,
            'aktif' => true,
        ]);
        $user->assignRole('sopir');

        return back()->with('success', "Akun sopir dibuat. Username: {$user->username} — Kata sandi awal: {$passwordAwal} (catat sekarang, tidak ditampilkan lagi).");
    }

    public function resetPassword(Karyawan $karyawan)
    {
        $user = $karyawan->user;
        if (!$user) {
            return back()->withErrors(['akun' => 'Karyawan ini belum memiliki akun.']);
        }

        $passwordBaru = Str::password(10, symbols: false);
        $user->update([
            'password' => Hash::make($passwordBaru),
            'wajib_ganti_password' => true,
        ]);

        return back()->with('success', "Kata sandi {$user->username} direset menjadi: {$passwordBaru} (wajib diganti saat login).");
    }
}
