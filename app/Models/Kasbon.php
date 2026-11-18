<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kasbon extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'kasbons';
    protected $fillable = ['cabang_id', 'karyawan_id', 'tanggal', 'jumlah', 'cicilan_per_bulan', 'sisa', 'status', 'jurnal_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah' => 'decimal:2', 'cicilan_per_bulan' => 'decimal:2', 'sisa' => 'decimal:2'];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
