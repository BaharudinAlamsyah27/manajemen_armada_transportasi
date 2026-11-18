<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagihanPemasok extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'tagihan_pemasoks';
    protected $fillable = ['cabang_id', 'no_tagihan', 'pemasok_id', 'tanggal', 'jatuh_tempo', 'jumlah', 'terbayar', 'status', 'sumber_type', 'sumber_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jatuh_tempo' => 'date', 'jumlah' => 'decimal:2', 'terbayar' => 'decimal:2'];
    }

    public function pemasok(): BelongsTo
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id');
    }
}
