<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananStatusLog extends Model
{
    protected $fillable = [
        'pesanan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'user_id',
        'waktu',
        'sumber',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
