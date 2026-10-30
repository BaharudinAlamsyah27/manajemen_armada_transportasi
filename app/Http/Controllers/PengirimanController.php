<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Services\PengirimanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengirimanController extends Controller
{
    /**
     * List semua manifest (pengiriman) untuk cabang pengguna.
     */
    public function index()
    {
        $cabangId = Auth::user()->cabang_id;
        $pengirimans = Pengiriman::where('cabang_id', $cabangId)->with(['kendaraan', 'sopir', 'pesanans'])->get();
        return view('pengiriman.index', compact('pengirimans'));
    }

    /**
     * Show form untuk membuat manifest baru.
     */
    public function create()
    {
        // Data kendaraan tersedia dan status "tersedia"
        $kendaraans = Auth::user()->cabang->kendaraans()->where('status', 'tersedia')->get();
        $sopirs = Auth::user()->cabang->sopirs()->get();
        $pesanans = Auth::user()->cabang->pesanans()->where('status', 'menunggu_jadwal')->get();
        return view('pengiriman.create', compact('kendaraans', 'sopirs', 'pesanans'));
    }

    /**
     * Store manifest baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'sopir_id'     => 'required|exists:sopirs,id',
            'pesanan_ids'  => 'required|array|min:1',
        ]);

        $service = new PengirimanService();
        $pengiriman = $service->createManifest($validated, $validated['pesanan_ids']);

        return redirect()->route('pengiriman.show', $pengiriman->id)
                         ->with('success', 'Manifest berhasil dibuat.');
    }

    /**
     * Tampilkan detail manifest.
     */
    public function show(Pengiriman $pengiriman)
    {
        $this->authorize('view', $pengiriman);
        $pengiriman->load(['kendaraan', 'sopir', 'pesanans']);
        return view('pengiriman.show', compact('pengiriman'));
    }
}
