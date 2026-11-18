<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anggaran extends Model
{
    use BelongsToCabang;

    protected $table = 'anggarans';
    protected $fillable = ['cabang_id', 'tahun', 'bulan', 'akun_id', 'jumlah'];

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2'];
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_id');
    }
}
