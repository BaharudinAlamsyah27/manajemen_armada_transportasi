<?php

namespace App\Services;

use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\ServisKendaraan;

class LaporanOperasionalService
{
    /**
     * Hitung biaya & kinerja armada sesuai Pedoman bagian 6.6.
     * Hanya data berstatus 'terverifikasi' yang dihitung; tidak termasuk gaji.
     */
    public function ringkasanPerKendaraan(string $dari, string $sampai): array
    {
        $kendaraans = Kendaraan::with('cabang')->orderBy('plat_nomor')->get();
        $hasil = [];

        foreach ($kendaraans as $k) {
            $bbm = (float) BbmLog::where('kendaraan_id', $k->id)
                ->where('status_verifikasi', 'terverifikasi')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->sum('total');

            $servis = (float) ServisKendaraan::where('kendaraan_id', $k->id)
                ->where('status', 'selesai')
                ->whereBetween('tanggal_selesai', [$dari, $sampai])
                ->sum('total_biaya');

            $ops = (float) BiayaOperasional::where('kendaraan_id', $k->id)
                ->where('status_verifikasi', 'terverifikasi')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->sum('jumlah');

            $pengirimanSelesai = Pengiriman::where('kendaraan_id', $k->id)
                ->where('status', 'selesai')
                ->whereBetween('tanggal_rencana', [$dari, $sampai])
                ->get();

            $kmTempuh = (int) $pengirimanSelesai->sum(fn ($p) => max(0, (int) $p->odometer_akhir - (int) $p->odometer_awal));
            $totalBiaya = round($bbm + $servis + $ops, 2);
            $biayaPerKm = $kmTempuh > 0 ? round($totalBiaya / $kmTempuh, 2) : null;

            $hasil[] = [
                'kendaraan' => $k,
                'bbm' => $bbm,
                'servis' => $servis,
                'operasional' => $ops,
                'total_biaya' => $totalBiaya,
                'jumlah_pengiriman' => $pengirimanSelesai->count(),
                'km_tempuh' => $kmTempuh,
                'biaya_per_km' => $biayaPerKm,
            ];
        }

        $terkirim = Pesanan::where('status', 'terkirim')->whereBetween('tanggal_pesan', [$dari, $sampai])->count();
        $gagal = Pesanan::where('status', 'gagal_kirim')->whereBetween('tanggal_pesan', [$dari, $sampai])->count();
        $tingkatKeberhasilan = ($terkirim + $gagal) > 0 ? round(($terkirim / ($terkirim + $gagal)) * 100, 1) : 100.0;

        return [
            'per_kendaraan' => $hasil,
            'tingkat_keberhasilan' => $tingkatKeberhasilan,
        ];
    }
}
