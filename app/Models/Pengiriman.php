<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengiriman extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'pengirimans';

    protected $fillable = [
        'cabang_id',
        'kode',
        'tanggal_rencana',
        'kendaraan_id',
        'sopir_id',
        'kernet_id',
        'tujuan_ringkas',
        'waktu_mulai_rencana',
        'estimasi_tiba',
        'rit_ke',
        'odometer_awal',
        'odometer_akhir',
        'waktu_berangkat',
        'waktu_tiba',
        'status',
        'catatan',
        'dibuat_oleh',
        'override_alasan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
            'waktu_mulai_rencana' => 'datetime',
            'estimasi_tiba' => 'datetime',
            'waktu_berangkat' => 'datetime',
            'waktu_tiba' => 'datetime',
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

    public function kernet(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'kernet_id');
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'pengiriman_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
