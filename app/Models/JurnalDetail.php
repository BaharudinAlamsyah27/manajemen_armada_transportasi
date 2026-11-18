<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_details';
    protected $fillable = ['jurnal_id', 'akun_id', 'debit', 'kredit', 'keterangan', 'kendaraan_id', 'pengiriman_id', 'pelanggan_id'];

    protected function casts(): array
    {
        return ['debit' => 'decimal:2', 'kredit' => 'decimal:2'];
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_id');
    }
}
