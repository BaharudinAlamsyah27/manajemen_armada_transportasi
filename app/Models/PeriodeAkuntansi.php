<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeAkuntansi extends Model
{
    protected $table = 'periode_akuntansis';
    protected $fillable = ['tahun', 'bulan', 'status', 'ditutup_oleh', 'ditutup_pada', 'alasan_buka_kembali'];

    protected function casts(): array
    {
        return ['ditutup_pada' => 'datetime'];
    }
}
