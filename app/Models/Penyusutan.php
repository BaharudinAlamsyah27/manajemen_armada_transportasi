<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyusutan extends Model
{
    protected $table = 'penyusutans';
    protected $fillable = ['aset_id', 'periode_id', 'jumlah', 'jurnal_id'];

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2'];
    }
}
