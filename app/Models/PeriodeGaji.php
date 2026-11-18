<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeGaji extends Model
{
    use BelongsToCabang;

    protected $table = 'periode_gajis';
    protected $fillable = ['cabang_id', 'tahun', 'bulan', 'status', 'dibuat_oleh', 'disetujui_oleh', 'tanggal_bayar', 'akun_kas_id'];

    protected function casts(): array
    {
        return ['tanggal_bayar' => 'date'];
    }

    public function slips(): HasMany
    {
        return $this->hasMany(SlipGaji::class, 'periode_gaji_id');
    }
}
