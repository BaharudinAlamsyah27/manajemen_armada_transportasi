<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Pengumuman::with(['cabang', 'pembuat'])->withCount('pembacas')
            ->orderByDesc('disematkan')
            ->latest('terbit_pada');

        if (!$user->hasRole('admin_pusat')) {
            $query->where(function ($q) use ($user) {
                $q->where('cabang_id', $user->cabang_id)
                  ->orWhereNull('cabang_id');
            });
        }

        $pengumumans = $query->get();
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();

        return view('pengumuman.index', compact('pengumumans', 'cabangs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'cabang_id' => 'nullable|exists:cabangs,id',
            'judul' => 'required|string|max:150',
            'isi' => 'required|string',
            'kategori' => 'required|in:info,keselamatan,kebijakan,darurat',
            'target_peran' => 'nullable|array',
            'terbit_pada' => 'required|date',
            'kedaluwarsa_pada' => 'nullable|date|after_or_equal:terbit_pada',
        ]);

        // Admin cabang hanya boleh mengumumkan untuk cabangnya sendiri.
        $cabangId = $user->hasRole('admin_pusat')
            ? ($validated['cabang_id'] ?: null)
            : $user->cabang_id;

        Pengumuman::create([
            'cabang_id' => $cabangId,
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'kategori' => $validated['kategori'],
            'target_peran' => $validated['target_peran'] ?? ['sopir', 'admin_cabang'],
            'disematkan' => $validated['kategori'] === 'darurat' || $request->boolean('disematkan'),
            'terbit_pada' => $validated['terbit_pada'],
            'kedaluwarsa_pada' => $validated['kedaluwarsa_pada'] ?? null,
            'dibuat_oleh' => $user->id,
        ]);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diterbitkan.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin_pusat') && (int) $pengumuman->cabang_id !== (int) $user->cabang_id) {
            abort(403, 'Tidak berhak menghapus pengumuman cabang lain.');
        }

        $pengumuman->delete();
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman dihapus.');
    }
}
