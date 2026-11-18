<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiPengiriman extends Model
{
    protected $table = 'bukti_pengirimans';

    protected $fillable = [
        'pesanan_id',
        'pengiriman_id',
        'jenis',
        'path',
        'nama_penerima',
        'catatan',
        'diambil_oleh',
        'waktu',
        'latitude',
        'longitude',
        'ukuran',
        'mime',
    ];

    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }

    public function pengambil(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diambil_oleh');
    }
}
