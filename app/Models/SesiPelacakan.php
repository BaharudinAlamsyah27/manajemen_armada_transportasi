<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiPelacakan extends Model
{
    protected $table = 'sesi_pelacakan';
    protected $fillable = ['pengiriman_id', 'kendaraan_id', 'sopir_id', 'mulai', 'selesai', 'status', 'persetujuan_sopir_pada'];

    protected function casts(): array
    {
        return ['mulai' => 'datetime', 'selesai' => 'datetime', 'persetujuan_sopir_pada' => 'datetime'];
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function pings(): HasMany
    {
        return $this->hasMany(LokasiPing::class, 'sesi_id');
    }
}
