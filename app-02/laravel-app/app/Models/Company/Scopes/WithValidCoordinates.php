<?php

namespace App\Models\Company\Scopes;

use App\Types\Coordinates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WithValidCoordinates implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereBetween('latitude', [Coordinates::MIN_LATITUDE, Coordinates::MAX_LATITUDE]);
        $builder->whereBetween('longitude', [Coordinates::MIN_LONGITUDE, Coordinates::MAX_LONGITUDE]);
    }
}
