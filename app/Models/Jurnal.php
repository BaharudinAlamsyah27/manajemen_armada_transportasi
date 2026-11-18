<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurnal extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'jurnals';
    protected $fillable = [
        'cabang_id', 'no_jurnal', 'tanggal', 'periode_id', 'keterangan', 'jenis',
        'peristiwa', 'sumber_type', 'sumber_id', 'status', 'membalik_jurnal_id',
        'dibuat_oleh', 'disetujui_oleh', 'diposting_pada',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'diposting_pada' => 'datetime'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkuntansi::class, 'periode_id');
    }
}
