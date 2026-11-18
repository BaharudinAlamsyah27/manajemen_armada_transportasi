<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengumuman extends Model
{
    use SoftDeletes;

    protected $table = 'pengumumans';

    protected $fillable = [
        'cabang_id',
        'judul',
        'isi',
        'kategori',
        'target_peran',
        'disematkan',
        'terbit_pada',
        'kedaluwarsa_pada',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'target_peran' => 'array',
            'disematkan' => 'boolean',
            'terbit_pada' => 'date',
            'kedaluwarsa_pada' => 'date',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pembacas(): HasMany
    {
        return $this->hasMany(PengumumanDibaca::class, 'pengumuman_id');
    }
}
