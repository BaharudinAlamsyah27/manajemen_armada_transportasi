<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Pesanan;
use App\Services\PesananService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index()
    {
        $pesanans = Pesanan::with(['cabang', 'pengiriman'])->latest()->get();
        return view('pesanan.index', compact('pesanans'));
    }

    public function create()
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        return view('pesanan.create', compact('cabangs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'cabang_id' => $user->hasRole('admin_pusat') ? 'required|exists:cabangs,id' : 'nullable|exists:cabangs,id',
            'nama_pengirim' => 'required|string|max:100',
            'no_hp_pengirim' => 'nullable|string|max:30',
            'alamat_asal' => 'required|string',
            'kota_asal' => 'required|string|max:100',
            'nama_penerima' => 'required|string|max:100',
            'no_hp_penerima' => 'required|string|max:30',
            'alamat_tujuan' => 'required|string',
            'kota_tujuan' => 'required|string|max:100',
            'deskripsi_barang' => 'nullable|string',
            'berat_kg' => 'required|numeric|min:0.1',
            'jumlah_koli' => 'required|integer|min:1',
            'tarif' => 'nullable|numeric|min:0',
        ]);

        $service = new PesananService();
        $pesanan = $service->createPesanan($validated);

        return redirect()->route('pesanan.show', $pesanan)->with('success', "Pesanan {$pesanan->kode_resi} berhasil dibuat.");
    }

    public function show(Pesanan $pesanan)
    {
        $pesanan->load(['cabang', 'pengiriman.kendaraan', 'pengiriman.sopir', 'statusLogs.user', 'buktiPengirimans']);
        $transisi = PesananService::transisiManual($pesanan->status);

        return view('pesanan.show', compact('pesanan', 'transisi'));
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $validated = $request->validate([
            'status_baru' => 'required|string',
            'catatan' => 'nullable|string|max:500',
        ]);

        // Transisi terjadwal/dalam_perjalanan/terkirim hanya lewat alur pengiriman.
        $bolehManual = in_array($validated['status_baru'], PesananService::transisiManual($pesanan->status), true);
        $koreksiPusat = in_array($pesanan->status, ['terkirim', 'dibatalkan'], true) && Auth::user()->hasRole('admin_pusat');

        if (!$bolehManual && !$koreksiPusat) {
            return back()->withErrors(['status_baru' => 'Perubahan status ini tidak dapat dilakukan dari halaman pesanan.']);
        }

        try {
            $service = new PesananService();
            $service->updateStatus($pesanan, $validated['status_baru'], $validated['catatan'] ?? null);
            return redirect()->route('pesanan.show', $pesanan)->with('success', 'Status pesanan berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['status_baru' => $e->getMessage()]);
        }
    }
}
