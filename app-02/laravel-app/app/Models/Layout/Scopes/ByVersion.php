<?php

namespace App\Models\Layout\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByVersion implements Scope
{
    private string $version;

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function apply(Builder $builder, Model $model)
    {
        $firstDigit = substr($this->version, 0, 1);

        $builder->where('version', '<=', $this->version);
        $builder->where('version', 'LIKE', $firstDigit . '%');
    }
}
