<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Kendaraan;
use App\Services\NomorDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KendaraanController extends Controller
{
    public function index()
    {
        $kendaraans = Kendaraan::with('cabang')->orderBy('kode')->get();
        return view('kendaraan.index', compact('kendaraans'));
    }

    public function create()
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        return view('kendaraan.form', ['kendaraan' => new Kendaraan(), 'cabangs' => $cabangs]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'cabang_id' => $user->hasRole('admin_pusat') ? 'required|exists:cabangs,id' : 'nullable|exists:cabangs,id',
            'plat_nomor' => 'required|string|max:20|unique:kendaraans,plat_nomor',
            'jenis' => 'required|in:motor,pickup,box,truk',
            'merk' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1990|max:2100',
            'kapasitas_kg' => 'required|numeric|min:1',
            'jenis_bbm' => 'nullable|string|max:30',
            'odometer_terakhir' => 'nullable|integer|min:0',
            'status' => 'required|in:tersedia,bertugas,servis,nonaktif',
            'stnk_berlaku_sampai' => 'nullable|date',
            'kir_berlaku_sampai' => 'nullable|date',
            'pajak_berlaku_sampai' => 'nullable|date',
        ]);

        $cabangId = $user->hasRole('admin_pusat') ? (int) $validated['cabang_id'] : (int) $user->cabang_id;
        $validated['cabang_id'] = $cabangId;
        $validated['kode'] = NomorDokumenService::buat($cabangId, 'KND');
        $validated['plat_nomor'] = strtoupper($validated['plat_nomor']);
        $validated['odometer_terakhir'] = $validated['odometer_terakhir'] ?? 0;

        Kendaraan::create($validated);

        return redirect()->route('kendaraan.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Kendaraan $kendaraan)
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        return view('kendaraan.form', compact('kendaraan', 'cabangs'));
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $validated = $request->validate([
            'plat_nomor' => 'required|string|max:20|unique:kendaraans,plat_nomor,' . $kendaraan->id,
            'jenis' => 'required|in:motor,pickup,box,truk',
            'merk' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1990|max:2100',
            'kapasitas_kg' => 'required|numeric|min:1',
            'jenis_bbm' => 'nullable|string|max:30',
            'odometer_terakhir' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,bertugas,servis,nonaktif',
            'stnk_berlaku_sampai' => 'nullable|date',
            'kir_berlaku_sampai' => 'nullable|date',
            'pajak_berlaku_sampai' => 'nullable|date',
        ]);

        $validated['plat_nomor'] = strtoupper($validated['plat_nomor']);
        $kendaraan->update($validated);

        return redirect()->route('kendaraan.index')->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan)
    {
        if ($kendaraan->pengirimans()->exists() || $kendaraan->bbmLogs()->exists() || $kendaraan->servisKendaraans()->exists()) {
            $kendaraan->update(['status' => 'nonaktif']);
            return redirect()->route('kendaraan.index')->with('success', 'Kendaraan memiliki riwayat operasional, status diubah menjadi nonaktif.');
        }

        $kendaraan->delete();
        return redirect()->route('kendaraan.index')->with('success', 'Kendaraan berhasil dihapus.');
    }
}
