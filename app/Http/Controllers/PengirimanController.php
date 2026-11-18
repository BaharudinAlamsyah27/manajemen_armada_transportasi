<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Services\PengirimanService;
use Exception;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index()
    {
        $pengirimans = Pengiriman::with(['cabang', 'kendaraan', 'sopir', 'pesanans'])->latest()->get();
        return view('pengiriman.index', compact('pengirimans'));
    }

    public function create()
    {
        $kendaraans = Kendaraan::where('status', 'tersedia')->orderBy('kode')->get();
        $sopirs = Karyawan::where('jabatan', 'sopir')->where('aktif', true)->orderBy('nama')->get();
        $pesanans = Pesanan::where('status', 'menunggu_jadwal')->orderBy('id')->get();
        return view('pengiriman.create', compact('kendaraans', 'sopirs', 'pesanans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_rencana' => 'required|date',
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'sopir_id' => 'required|exists:karyawans,id',
            'tujuan_ringkas' => 'nullable|string|max:150',
            'pesanan_ids' => 'required|array|min:1',
            'pesanan_ids.*' => 'exists:pesanans,id',
            'catatan' => 'nullable|string',
            'override_alasan' => 'nullable|string',
        ]);

        try {
            $service = new PengirimanService();
            $pengiriman = $service->createManifest($validated, $validated['pesanan_ids']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', "Manifest {$pengiriman->kode} berhasil dijadwalkan.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['manifest' => $e->getMessage()]);
        }
    }

    public function show(Pengiriman $pengiriman)
    {
        $pengiriman->load(['cabang', 'kendaraan', 'sopir', 'pesanans']);
        return view('pengiriman.show', compact('pengiriman'));
    }

    public function mulai(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            'odometer_awal' => 'required|integer|min:0',
        ]);

        try {
            $service = new PengirimanService();
            $service->mulaiPerjalanan($pengiriman, (int) $validated['odometer_awal']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman berhasil diberangkatkan.');
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    public function selesai(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            'odometer_akhir' => 'required|integer|min:0',
        ]);

        try {
            $service = new PengirimanService();
            $service->selesaiPerjalanan($pengiriman, (int) $validated['odometer_akhir']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman telah diselesaikan.');
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }
}
