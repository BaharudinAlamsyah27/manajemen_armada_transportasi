<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumumanDibaca extends Model
{
    protected $table = 'pengumuman_dibacas';

    protected $fillable = [
        'pengumuman_id',
        'user_id',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_pada' => 'datetime',
        ];
    }

    public function pengumuman(): BelongsTo
    {
        return $this->belongsTo(Pengumuman::class, 'pengumuman_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
