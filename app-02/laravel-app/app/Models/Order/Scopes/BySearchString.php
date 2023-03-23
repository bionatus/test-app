<?php

namespace App\Models\Order\Scopes;

use App\Models\Company;
use App\Models\Order;
use App\Models\Scopes\BySearchString as ModelsBySearchString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySearchString implements Scope
{
    private string $searchString;

    public function __construct(string $searchString)
    {
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(function(Builder $builder) {
            $builder->orWhere(function(Builder $builder) {
                $builder->scoped(new ModelsBySearchString($this->searchString, Order::tableName() . '.name'));
            });
            $builder->orWhere(function(Builder $builder) {
                $builder->scoped(new ModelsBySearchString($this->searchString, 'bid_number'));
            });
            $builder->orWhereHas('user', function(Builder $builder) {
                $builder->scoped(new ModelsBySearchString($this->searchString, 'first_name'));
                $builder->orWhere(function(Builder $builder) {
                    $builder->scoped(new ModelsBySearchString($this->searchString, 'last_name'));
                });
            });
            $builder->orWhereHas('user.company', function(Builder $builder) {
                $builder->scoped(new ModelsBySearchString($this->searchString, Company::tableName() . '.name'));
            });
        });
    }
}
