<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranAlokasi extends Model
{
    protected $table = 'pembayaran_alokasis';
    protected $fillable = ['pembayaran_id', 'invoice_id', 'jumlah_alokasi', 'potongan_pajak'];

    protected function casts(): array
    {
        return ['jumlah_alokasi' => 'decimal:2', 'potongan_pajak' => 'decimal:2'];
    }
}
