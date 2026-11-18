<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;

class PembayaranPemasok extends Model
{
    use BelongsToCabang;

    protected $table = 'pembayaran_pemasoks';
    protected $fillable = ['cabang_id', 'tagihan_id', 'tanggal', 'jumlah', 'akun_kas_id', 'referensi'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah' => 'decimal:2'];
    }
}
