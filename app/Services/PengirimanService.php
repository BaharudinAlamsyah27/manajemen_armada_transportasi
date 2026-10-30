<?php

namespace App\Services;

use App\Models\Pengiriman;
use App\Services\PesananService;
use App\Models\Pesanan;
use App\Models\Kendaraan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PengirimanService
{
    /**
     * Memasukkan beberapa pesanan ke dalam satu kendaraan (Manifest/Pengiriman)
     */
    public function createManifest(array $data, array $pesananIds)
    {
        return DB::transaction(function () use ($data, $pesananIds) {
            // 1. Cek Kendaraan
            $kendaraan = Kendaraan::findOrFail($data['kendaraan_id']);

            if ($kendaraan->status !== 'tersedia') {
                throw new Exception("Kendaraan sedang tidak tersedia.");
            }

            // 2. Verifikasi Dokumen Kendaraan (KIR / STNK)
            if ($kendaraan->kir_berlaku_sampai < now() || $kendaraan->stnk_berlaku_sampai < now()) {
                throw new Exception("Kendaraan tidak laik jalan (Dokumen Kadaluarsa).");
            }

            // 3. Kalkulasi Muatan (Mencegah Overload)
            $pesanans = Pesanan::whereIn('id', $pesananIds)
                ->where('status', 'menunggu_jadwal')
                ->get();

            if ($pesanans->isEmpty()) {
                throw new Exception("Pesanan tidak valid atau sudah dijadwalkan.");
            }

            $totalMuatan = $pesanans->sum('berat_kg');
            if ($totalMuatan > $kendaraan->kapasitas_kg) {
                throw new Exception("Overload: Total muatan ($totalMuatan kg) melebihi kapasitas kendaraan ({$kendaraan->kapasitas_kg} kg).");
            }

            // 4. Generate Nomor Surat Jalan (Manifest)
            $user = Auth::user();
            $kodeCabang = $user->cabang ? $user->cabang->kode : 'PST';
            $noSuratJalan = 'SJ-' . $kodeCabang . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // 5. Buat Pengiriman (Manifest)
            $pengiriman = Pengiriman::create([
                'cabang_id' => $user->cabang_id,
                'nomor_surat_jalan' => $noSuratJalan,
                'kendaraan_id' => $kendaraan->id,
                'sopir_id' => $data['sopir_id'],
                'status' => 'diproses',
                'waktu_berangkat' => null,
            ]);

            // 6. Asosiasikan Pesanan & Update Status
            $pesananService = new PesananService();
            foreach ($pesanans as $pesanan) {
                $pesanan->update(['pengiriman_id' => $pengiriman->id]);
                $pesananService->updateStatus(
                    $pesanan,
                    'dalam_perjalanan',
                    "Pesanan dimasukkan ke manifes {$noSuratJalan} dan siap diberangkatkan."
                );
            }

            // 7. Tandai kendaraan sedang digunakan
            $kendaraan->update(['status' => 'dalam_perjalanan']);

            return $pengiriman;
        });
    }
}
