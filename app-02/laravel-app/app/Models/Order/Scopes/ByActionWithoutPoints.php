<?php

namespace App\Models\Order\Scopes;

use App\Models\Point\Scopes\ByAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByActionWithoutPoints implements Scope
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereDoesntHave('points', function(Builder $builder) {
            $builder->scoped(new ByAction($this->action));
        });
    }
}
