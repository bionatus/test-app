<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\GasValve;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\GasValveResource as GasValveResourceModel;

/**
 * @property GasValve $resource
 */
class GasValveResource extends JsonResource implements HasJsonSchema
{
    private GasValveResourceModel $gasValveResource;

    public function __construct(GasValve $resource)
    {
        parent::__construct($resource);
        $this->gasValveResource = new GasValveResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->gasValveResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return GasValveResourceModel::jsonSchema();
    }
}
