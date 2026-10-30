<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang()
    {
        static::addGlobalScope('cabang', function (Builder $builder) {
            $user = Auth::user();
            // Jika user login dan bukan admin pusat
            if ($user && !$user->hasRole('admin_pusat')) {
                $builder->where($builder->getModel()->getTable() . '.cabang_id', $user->cabang_id);
            }
        });

        static::creating(function (Model $model) {
            if (!$model->cabang_id && Auth::check() && Auth::user()->cabang_id) {
                $model->cabang_id = Auth::user()->cabang_id;
            }
        });
    }
}