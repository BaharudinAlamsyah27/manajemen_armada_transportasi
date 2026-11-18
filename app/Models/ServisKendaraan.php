<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServisKendaraan extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'kendaraan_id',
        'jenis',
        'status',
        'tanggal_masuk',
        'tanggal_selesai',
        'odometer',
        'bengkel',
        'biaya_jasa',
        'total_biaya',
        'deskripsi',
        'servis_berikutnya_km',
        'servis_berikutnya_tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_selesai' => 'date',
            'odometer' => 'integer',
            'biaya_jasa' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'servis_berikutnya_km' => 'integer',
            'servis_berikutnya_tanggal' => 'date',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServisSukuCadang::class, 'servis_id');
    }
}
