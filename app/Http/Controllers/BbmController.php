<?php

namespace App\Http\Controllers;

use App\Models\BbmLog;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BbmController extends Controller
{
    public function index()
    {
        $bbmLogs = BbmLog::with(['cabang', 'kendaraan', 'sopir'])->latest('tanggal')->get();
        $kendaraans = Kendaraan::orderBy('kode')->get();
        $sopirs = Karyawan::where('jabatan', 'sopir')->where('aktif', true)->orderBy('nama')->get();

        return view('bbm.index', compact('bbmLogs', 'kendaraans', 'sopirs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'sopir_id' => 'nullable|exists:karyawans,id',
            'tanggal' => 'required|date',
            'liter' => 'required|numeric|min:0.1',
            'harga_per_liter' => 'required|numeric|min:1',
            'odometer' => 'required|integer|min:0',
            'tangki_penuh' => 'nullable|boolean',
            'spbu' => 'nullable|string|max:100',
        ]);

        $kendaraan = Kendaraan::findOrFail($validated['kendaraan_id']);

        if ((int) $validated['odometer'] < (int) $kendaraan->odometer_terakhir && !Auth::user()->hasRole('admin_pusat')) {
            return back()->withInput()->withErrors([
                'odometer' => "Odometer tidak boleh lebih kecil dari odometer terakhir kendaraan ({$kendaraan->odometer_terakhir} km).",
            ]);
        }

        $total = round((float) $validated['liter'] * (float) $validated['harga_per_liter'], 2);

        BbmLog::create([
            'cabang_id' => $kendaraan->cabang_id,
            'kendaraan_id' => $kendaraan->id,
            'sopir_id' => $validated['sopir_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'liter' => $validated['liter'],
            'harga_per_liter' => $validated['harga_per_liter'],
            'total' => $total,
            'odometer' => $validated['odometer'],
            'tangki_penuh' => $request->boolean('tangki_penuh'),
            'spbu' => $validated['spbu'] ?? null,
            'dicatat_oleh' => Auth::id(),
            'sumber' => Auth::user()->hasRole('sopir') ? 'sopir' : 'admin',
            'status_verifikasi' => Auth::user()->hasRole('sopir') ? 'menunggu_verifikasi' : 'terverifikasi',
        ]);

        if ((int) $validated['odometer'] > (int) $kendaraan->odometer_terakhir) {
            $kendaraan->update(['odometer_terakhir' => (int) $validated['odometer']]);
        }

        return redirect()->route('bbm.index')->with('success', 'Catatan BBM berhasil disimpan.');
    }
}
