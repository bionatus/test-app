<?php

namespace App\Models\OrderSubstatus\Scopes;

use App\Models\OrderSubstatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByLastOfOrder implements Scope
{
    private string $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $tableName = OrderSubstatus::tableName();
        $id        = OrderSubstatus::keyName();

        $builder->whereRaw("id = (SELECT max($id) FROM $tableName where order_id = $this->id)");
    }
}
