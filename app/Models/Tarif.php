<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarif extends Model
{
    use SoftDeletes;

    protected $table = 'tarifs';
    protected $fillable = ['kota_asal', 'kota_tujuan', 'jenis_kendaraan', 'harga_per_kg', 'tarif_minimum', 'berlaku_sejak', 'aktif'];

    protected function casts(): array
    {
        return ['harga_per_kg' => 'decimal:2', 'tarif_minimum' => 'decimal:2', 'berlaku_sejak' => 'date', 'aktif' => 'boolean'];
    }
}
