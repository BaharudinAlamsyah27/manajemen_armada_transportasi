<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsetTetap extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $table = 'aset_tetaps';
    protected $fillable = ['cabang_id', 'kendaraan_id', 'nama', 'tanggal_perolehan', 'harga_perolehan', 'nilai_residu', 'umur_bulan', 'metode', 'akumulasi_penyusutan', 'status'];

    protected function casts(): array
    {
        return ['tanggal_perolehan' => 'date', 'harga_perolehan' => 'decimal:2', 'nilai_residu' => 'decimal:2', 'akumulasi_penyusutan' => 'decimal:2'];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }
}
