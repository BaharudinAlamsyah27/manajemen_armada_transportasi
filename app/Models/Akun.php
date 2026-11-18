<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Akun extends Model
{
    protected $table = 'akuns';
    protected $fillable = ['kode', 'nama', 'tipe', 'saldo_normal', 'induk_id', 'kategori_arus_kas', 'is_kas', 'aktif', 'sistem'];

    protected function casts(): array
    {
        return ['is_kas' => 'boolean', 'aktif' => 'boolean', 'sistem' => 'boolean'];
    }

    public function jurnalDetails(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'akun_id');
    }
}
