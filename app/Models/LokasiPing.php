<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiPing extends Model
{
    protected $table = 'lokasi_pings';
    protected $fillable = [
        'sesi_id', 'kendaraan_id', 'latitude', 'longitude', 'akurasi_m',
        'kecepatan_kmh', 'arah', 'waktu_perangkat', 'waktu_server', 'sumber', 'valid',
    ];

    protected function casts(): array
    {
        return ['waktu_perangkat' => 'datetime', 'waktu_server' => 'datetime', 'valid' => 'boolean'];
    }
}
