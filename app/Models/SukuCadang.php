<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SukuCadang extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga_standar',
        'stok_minimum',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_standar' => 'decimal:2',
            'stok_minimum' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function stokSukuCadangs(): HasMany
    {
        return $this->hasMany(StokSukuCadang::class, 'suku_cadang_id');
    }

    public function mutasiSukuCadangs(): HasMany
    {
        return $this->hasMany(MutasiSukuCadang::class, 'suku_cadang_id');
    }
}
