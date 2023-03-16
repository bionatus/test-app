<?php

namespace App\Models\Staff\Scopes;

use App\Models\OrderStaff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LastAssigned implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByDesc(OrderStaff::query()->select('created_at')
            ->whereColumn('staff.id', '=', 'order_staff.staff_id')
            ->latest()->limit(1));
    }
}
