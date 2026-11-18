<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenGaji extends Model
{
    protected $table = 'komponen_gajis';
    protected $fillable = ['kode', 'nama', 'jenis', 'tipe_hitung', 'nominal_default', 'akun_id', 'aktif'];

    protected function casts(): array
    {
        return ['nominal_default' => 'decimal:2', 'aktif' => 'boolean'];
    }
}
