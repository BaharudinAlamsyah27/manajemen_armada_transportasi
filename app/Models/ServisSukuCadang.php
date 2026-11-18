<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServisSukuCadang extends Model
{
    protected $fillable = [
        'servis_id',
        'suku_cadang_id',
        'qty',
        'harga_satuan',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'decimal:2',
        ];
    }

    public function servis(): BelongsTo
    {
        return $this->belongsTo(ServisKendaraan::class, 'servis_id');
    }

    public function sukuCadang(): BelongsTo
    {
        return $this->belongsTo(SukuCadang::class, 'suku_cadang_id');
    }
}
