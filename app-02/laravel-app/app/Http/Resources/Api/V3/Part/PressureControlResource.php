<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\PressureControl;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\PressureControlResource as PressureControlResourceModel;

/**
 * @property PressureControl $resource
 */
class PressureControlResource extends JsonResource implements HasJsonSchema
{
    private PressureControlResourceModel $pressureControlResource;

    public function __construct(PressureControl $resource)
    {
        parent::__construct($resource);
        $this->pressureControlResource = new PressureControlResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->pressureControlResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return PressureControlResourceModel::jsonSchema();
    }
}
