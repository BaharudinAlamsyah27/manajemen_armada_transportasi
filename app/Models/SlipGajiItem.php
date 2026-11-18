<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipGajiItem extends Model
{
    protected $table = 'slip_gaji_items';
    protected $fillable = ['slip_id', 'komponen_id', 'keterangan', 'jenis', 'jumlah'];

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2'];
    }
}
