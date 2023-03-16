<?php

namespace App\Scopes;

use Illuminate\Database\Query\Builder;

class ByType implements Scope
{
    private ?string $type;

    public function __construct(?string $type)
    {
        $this->type = $type;
    }

    public function apply(Builder $builder): void
    {
        if ($this->type) {
            $builder->where('type', $this->type);
        }
    }
}
