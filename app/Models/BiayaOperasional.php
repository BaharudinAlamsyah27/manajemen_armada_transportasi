<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiayaOperasional extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'kendaraan_id',
        'pengiriman_id',
        'jenis',
        'tanggal',
        'jumlah',
        'keterangan',
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
            'jumlah' => 'decimal:2',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
