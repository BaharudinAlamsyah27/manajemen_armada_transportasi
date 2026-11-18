<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiSukuCadang extends Model
{
    use BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'suku_cadang_id',
        'tipe',
        'qty',
        'harga_satuan',
        'referensi_type',
        'referensi_id',
        'keterangan',
        'user_id',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'decimal:2',
            'tanggal' => 'datetime',
        ];
    }

    public function sukuCadang(): BelongsTo
    {
        return $this->belongsTo(SukuCadang::class, 'suku_cadang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
