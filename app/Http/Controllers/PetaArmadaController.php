<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\SesiPelacakan;
use App\Services\LokasiService;
use Illuminate\Http\Request;

class PetaArmadaController extends Controller
{
    public function index()
    {
        $pengirimanBerjalan = Pengiriman::with(['kendaraan', 'sopir', 'cabang'])
            ->where('status', 'berjalan')
            ->get();

        $sesis = SesiPelacakan::with(['kendaraan', 'pengiriman.sopir'])
            ->where('status', 'aktif')
            ->get()
            ->map(function (SesiPelacakan $s) {
                $ping = $s->pings()->where('valid', true)->latest('waktu_server')->first();
                return [
                    'kendaraan' => $s->kendaraan?->plat_nomor,
                    'sopir' => $s->pengiriman?->sopir?->nama,
                    'kode' => $s->pengiriman?->kode,
                    'lat' => $ping?->latitude,
                    'lng' => $ping?->longitude,
                    'waktu' => $ping?->waktu_server?->format('H:i:s'),
                ];
            });

        return view('peta.index', compact('pengirimanBerjalan', 'sesis'));
    }

    public function simpanPing(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'pengiriman_id' => 'required|integer',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'akurasi_m' => 'nullable|numeric|min:0',
            'kecepatan_kmh' => 'nullable|numeric|min:0',
            'waktu_perangkat' => 'nullable|date',
        ]);

        $pengiriman = Pengiriman::where('id', $validated['pengiriman_id'])
            ->where('sopir_id', $user->karyawan_id)
            ->where('status', 'berjalan')
            ->first();

        if (!$pengiriman) {
            return response()->json(['message' => 'Tidak ada pengiriman berjalan milik Anda.'], 403);
        }

        $sesi = SesiPelacakan::firstOrCreate(
            ['pengiriman_id' => $pengiriman->id, 'status' => 'aktif'],
            [
                'kendaraan_id' => $pengiriman->kendaraan_id,
                'sopir_id' => $pengiriman->sopir_id,
                'mulai' => now(),
                'persetujuan_sopir_pada' => now(),
            ]
        );

        $ping = (new LokasiService())->catat($sesi, $validated);

        return response()->json(['ok' => true, 'valid' => $ping->valid]);
    }
}
