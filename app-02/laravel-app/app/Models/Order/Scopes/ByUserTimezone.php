<?php

namespace App\Models\Order\Scopes;

use App\Models\User\Scopes\ByTimezone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByUserTimezone implements Scope
{
    private ?string $timezone;

    public function __construct(?string $timezone)
    {
        $this->timezone = $timezone;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('user', function(Builder $query) {
            $query->scoped(new ByTimezone($this->timezone));
        });
    }
}
