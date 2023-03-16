<?php

namespace App\Models\Supplier\Scopes;

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

    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function($query) {
            $query->orWhere('name', 'LIKE', "%{$this->searchString}%")
                ->orWhere('address', 'LIKE', "%{$this->searchString}%")
                ->orWhere('city', 'LIKE', "%{$this->searchString}%")
                ->orWhere('zip_code', 'LIKE', "%{$this->searchString}%");
        });
    }
}
