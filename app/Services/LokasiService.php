<?php

namespace App\Services;

use App\Contracts\LokasiProvider;
use App\Models\LokasiPing;
use App\Models\SesiPelacakan;
use App\Models\Setting;
use Carbon\Carbon;

class LokasiService implements LokasiProvider
{
    public function catat(SesiPelacakan $sesi, array $data): LokasiPing
    {
        $maksAkurasi = (float) Setting::getValue('gps_akurasi_maks_m', 100);
        $akurasi = isset($data['akurasi_m']) ? (float) $data['akurasi_m'] : null;
        $waktuServer = now();
        $waktuPerangkat = isset($data['waktu_perangkat']) ? Carbon::parse($data['waktu_perangkat']) : $waktuServer;

        $valid = true;
        if ($akurasi !== null && $akurasi > $maksAkurasi) {
            $valid = false;
        }
        if (abs($waktuServer->diffInMinutes($waktuPerangkat)) > 5) {
            $valid = false;
        }

        return LokasiPing::create([
            'sesi_id' => $sesi->id,
            'kendaraan_id' => $sesi->kendaraan_id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'akurasi_m' => $akurasi,
            'kecepatan_kmh' => $data['kecepatan_kmh'] ?? null,
            'arah' => $data['arah'] ?? null,
            'waktu_perangkat' => $waktuPerangkat,
            'waktu_server' => $waktuServer,
            'sumber' => $data['sumber'] ?? 'web',
            'valid' => $valid,
        ]);
    }

    /**
     * Hitung jarak total (km) antar titik valid berurutan menggunakan rumus Haversine.
     */
    public static function hitungJarakHaversineKm($pings): float
    {
        $valid = $pings->where('valid', true)->values();
        $total = 0.0;
        for ($i = 1; $i < $valid->count(); $i++) {
            $total += self::haversine(
                (float) $valid[$i - 1]->latitude,
                (float) $valid[$i - 1]->longitude,
                (float) $valid[$i]->latitude,
                (float) $valid[$i]->longitude
            );
        }

        return round($total, 2);
    }

    public static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($a)));
    }
}
