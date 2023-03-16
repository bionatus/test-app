<?php

namespace App\Models\SupplierUser\Scopes;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;

class ByVisibleByUser implements Eloquent\Scope
{
    private bool $visibility;

    public function __construct(bool $visibility)
    {
        $this->visibility = $visibility;
    }

    public function apply(Eloquent\Builder $builder, Model $model): void
    {
        $builder->where('visible_by_user', $this->visibility);
    }
}
