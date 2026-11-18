<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use SoftDeletes;

    protected $table = 'pelanggans';
    protected $fillable = ['kode', 'nama', 'npwp', 'alamat', 'kontak', 'no_hp', 'termin_hari', 'aktif'];

    protected function casts(): array
    {
        return ['npwp' => 'encrypted', 'aktif' => 'boolean'];
    }
}
