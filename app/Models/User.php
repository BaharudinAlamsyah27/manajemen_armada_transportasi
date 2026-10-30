<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\BelongsToCabang;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, BelongsToCabang;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'cabang_id',
        'karyawan_id',
        'wajib_ganti_password',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
            'aktif' => 'boolean',
        ];
    }
}
