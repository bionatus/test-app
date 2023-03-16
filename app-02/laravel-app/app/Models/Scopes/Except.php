<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Except implements Scope
{
    private string $key;
    private        $value;

    public function __construct(string $key, $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($this->key, '<>', $this->value);
    }
}
