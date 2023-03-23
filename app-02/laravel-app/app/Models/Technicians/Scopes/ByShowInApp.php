<?php

namespace App\Models\Technicians\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByShowInApp implements Scope
{
    private bool $showInApp;

    public function __construct(bool $showInApp)
    {
        $this->showInApp = $showInApp;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('show_in_app', $this->showInApp);
    }
}
