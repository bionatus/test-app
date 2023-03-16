<?php

namespace App\Models\Term\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByRequiredAtRange implements Scope
{
    private ?string $requiredAt;

    public function __construct(?string $requiredAt)
    {
        $this->requiredAt = $requiredAt;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->requiredAt) {
            $builder->whereDate('required_at', '<=', $this->requiredAt);
        }
    }
}
