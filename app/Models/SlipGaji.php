<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGaji extends Model
{
    protected $table = 'slip_gajis';
    protected $fillable = ['periode_gaji_id', 'karyawan_id', 'no_slip', 'total_pendapatan', 'total_potongan', 'gaji_bersih', 'hari_hadir', 'jumlah_ritase', 'catatan'];

    protected function casts(): array
    {
        return ['total_pendapatan' => 'decimal:2', 'total_potongan' => 'decimal:2', 'gaji_bersih' => 'decimal:2'];
    }

    public function periodeGaji(): BelongsTo
    {
        return $this->belongsTo(PeriodeGaji::class, 'periode_gaji_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SlipGajiItem::class, 'slip_id');
    }
}
