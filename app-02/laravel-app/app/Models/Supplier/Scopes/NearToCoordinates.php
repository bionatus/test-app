<?php

namespace App\Models\Supplier\Scopes;

use App\Types\Coordinates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NearToCoordinates implements Scope
{
    const EARTH_RADIUS_MILES = 3958.8;
    private string $latitude;
    private string $longitude;

    public function __construct(string $latitude, string $longitude)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $minLatitude   = Coordinates::MIN_LATITUDE;
        $maxLatitude   = Coordinates::MAX_LATITUDE;
        $minLongitude  = Coordinates::MIN_LONGITUDE;
        $maxLongitude  = Coordinates::MAX_LONGITUDE;
        $tableName     = $model->getTable();
        $earthRadiusKm = self::EARTH_RADIUS_MILES;
        $sqlValid      = "({$tableName}.latitude between {$minLatitude} and {$maxLatitude}) AND ({$tableName}.longitude between {$minLongitude} and {$maxLongitude})";
        $sqlDistance   = "(
                {$earthRadiusKm} * ACOS(cos(radians({$this->latitude}))
                    * cos(radians({$tableName}.latitude))
                    * cos(radians({$tableName}.longitude) - radians({$this->longitude}))
                    + sin(radians({$this->latitude})) * sin(radians({$tableName}.latitude))
                )
            )";

        $builder->selectRaw("IF({$sqlValid}, {$sqlDistance}, null) AS distance, {$tableName}.*");
        $builder->orderByRaw('distance IS NULL');
        $builder->orderBy('distance');
    }
}
