<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Sensor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\SensorResource as SensorResourceModel;

/**
 * @property Sensor $resource
 */
class SensorResource extends JsonResource implements HasJsonSchema
{
    private SensorResourceModel $sensorResource;

    public function __construct(Sensor $resource)
    {
        parent::__construct($resource);
        $this->sensorResource = new SensorResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->sensorResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SensorResourceModel::jsonSchema();
    }
}
