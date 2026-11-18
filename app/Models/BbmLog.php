<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BbmLog extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'kendaraan_id',
        'sopir_id',
        'pengiriman_id',
        'tanggal',
        'liter',
        'harga_per_liter',
        'total',
        'odometer',
        'tangki_penuh',
        'spbu',
        'dicatat_oleh',
        'sumber',
        'status_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'alasan_tolak',
        'bukti_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'liter' => 'decimal:2',
            'harga_per_liter' => 'decimal:2',
            'total' => 'decimal:2',
            'odometer' => 'integer',
            'tangki_penuh' => 'boolean',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function sopir(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'sopir_id');
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
