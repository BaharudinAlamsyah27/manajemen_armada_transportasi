<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';
    protected $fillable = ['invoice_id', 'pesanan_id', 'deskripsi', 'qty', 'harga', 'jumlah'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:2', 'harga' => 'decimal:2', 'jumlah' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
