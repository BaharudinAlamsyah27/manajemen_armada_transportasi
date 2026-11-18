<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kendaraan extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'kode',
        'plat_nomor',
        'jenis',
        'merk',
        'model',
        'tahun',
        'kapasitas_kg',
        'jenis_bbm',
        'odometer_terakhir',
        'status',
        'stnk_berlaku_sampai',
        'kir_berlaku_sampai',
        'pajak_berlaku_sampai',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas_kg' => 'decimal:2',
            'odometer_terakhir' => 'integer',
            'stnk_berlaku_sampai' => 'date',
            'kir_berlaku_sampai' => 'date',
            'pajak_berlaku_sampai' => 'date',
        ];
    }

    public function pengirimans(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'kendaraan_id');
    }

    public function bbmLogs(): HasMany
    {
        return $this->hasMany(BbmLog::class, 'kendaraan_id');
    }

    public function servisKendaraans(): HasMany
    {
        return $this->hasMany(ServisKendaraan::class, 'kendaraan_id');
    }
}
