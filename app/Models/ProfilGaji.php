<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilGaji extends Model
{
    protected $table = 'profil_gajis';
    protected $fillable = ['karyawan_id', 'komponen_id', 'nominal', 'berlaku_sejak'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2', 'berlaku_sejak' => 'date'];
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenGaji::class, 'komponen_id');
    }
}
