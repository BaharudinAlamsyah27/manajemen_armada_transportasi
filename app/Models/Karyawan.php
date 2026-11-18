<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'kode',
        'nama',
        'jabatan',
        'no_hp',
        'no_sim',
        'jenis_sim',
        'sim_berlaku_sampai',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'no_sim' => 'encrypted',
            'sim_berlaku_sampai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    public function pengirimans(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'sopir_id');
    }
}
