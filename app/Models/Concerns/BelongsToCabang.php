<?php

namespace App\Models\Concerns;

use App\Models\Cabang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang(): void
    {
        static::addGlobalScope('cabang', function (Builder $builder) {
            if (!Auth::hasUser()) {
                return;
            }
            $user = Auth::user();
            if ($user && !$user->hasAnyRole(['admin_pusat', 'keuangan'])) {
                $builder->where($builder->getModel()->getTable() . '.cabang_id', $user->cabang_id);
            }
        });

        static::creating(function (Model $model) {
            if (!$model->cabang_id && Auth::hasUser() && Auth::user()->cabang_id) {
                $model->cabang_id = Auth::user()->cabang_id;
            }
        });
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
