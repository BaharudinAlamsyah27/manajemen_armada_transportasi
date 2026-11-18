<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cabang extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'kota',
        'alamat',
        'no_hp',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'cabang_id');
    }

    public function sopirs(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'cabang_id')->where('jabatan', 'sopir');
    }

    public function kendaraans(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'cabang_id');
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'cabang_id');
    }

    public function pengirimans(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'cabang_id');
    }
}
