<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'invoices';
    protected $fillable = [
        'cabang_id', 'no_invoice', 'pelanggan_id', 'tanggal', 'jatuh_tempo',
        'subtotal', 'ppn_persen', 'ppn_nominal', 'total', 'terbayar', 'status', 'catatan', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
            'subtotal' => 'decimal:2',
            'ppn_persen' => 'decimal:2',
            'ppn_nominal' => 'decimal:2',
            'total' => 'decimal:2',
            'terbayar' => 'decimal:2',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function sisa(): float
    {
        return max(0, round((float) $this->total - (float) $this->terbayar, 2));
    }
}
