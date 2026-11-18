<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokSukuCadang extends Model
{
    use BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'suku_cadang_id',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function sukuCadang(): BelongsTo
    {
        return $this->belongsTo(SukuCadang::class, 'suku_cadang_id');
    }
}
