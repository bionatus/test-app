<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\TemperatureControl;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\TemperatureControlResource as TemperatureControlResourceModel;

/**
 * @property TemperatureControl $resource
 */
class TemperatureControlResource extends JsonResource implements HasJsonSchema
{
    private TemperatureControlResourceModel $temperatureControlResource;

    public function __construct(TemperatureControl $resource)
    {
        parent::__construct($resource);
        $this->temperatureControlResource = new TemperatureControlResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->temperatureControlResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return TemperatureControlResourceModel::jsonSchema();
    }
}
