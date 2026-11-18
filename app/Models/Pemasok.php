<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pemasok extends Model
{
    use SoftDeletes;

    protected $table = 'pemasoks';
    protected $fillable = ['kode', 'nama', 'jenis', 'kontak', 'termin_hari', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
