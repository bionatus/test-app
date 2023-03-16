<?php

namespace App\Models\User\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTimezone implements Scope
{
    private ?string $timezone;

    public function __construct(?string $timezone)
    {
        $this->timezone = $timezone;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('timezone', $this->timezone);
    }
}
