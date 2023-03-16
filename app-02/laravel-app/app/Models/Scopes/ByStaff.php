<?php

namespace App\Models\Scopes;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByStaff implements Scope
{
    private Staff $staff;

    public function __construct(Staff $staff)
    {
        $this->staff = $staff;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('staff_id', $this->staff->getKey());
    }
}
