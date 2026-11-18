<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'pembayarans';
    protected $fillable = [
        'cabang_id', 'no_pembayaran', 'pelanggan_id', 'tanggal', 'metode',
        'akun_kas_id', 'jumlah_diterima', 'potongan_pajak', 'referensi', 'dicatat_oleh', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah_diterima' => 'decimal:2', 'potongan_pajak' => 'decimal:2'];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function alokasis(): HasMany
    {
        return $this->hasMany(PembayaranAlokasi::class, 'pembayaran_id');
    }
}
