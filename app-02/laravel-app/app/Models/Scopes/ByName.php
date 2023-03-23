<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByName implements Scope
{
    private ?string $name;
    private bool    $strict;

    public function __construct(?string $name, bool $strict = false)
    {
        $this->name   = $name;
        $this->strict = $strict;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->strict) {
            $builder->where('name', '=', $this->name);
        } else {
            $builder->where('name', 'LIKE', "%{$this->name}%");
        }
    }
}
