<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $request = request();

        $tenantId = $request->attributes->get('tenant_id')
            ?: (Auth::check() ? Auth::user()->tenant_id : null);

        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}
