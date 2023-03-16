<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySearchString implements Scope
{
    private ?string $searchString;
    private string  $field;

    public function __construct(?string $searchString, string $field = 'name')
    {
        $this->searchString = $searchString;
        $this->field        = $field;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->searchString) {
            $builder->where($this->field, 'LIKE', "%{$this->searchString}%");
        }
    }
}
