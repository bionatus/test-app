<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\AirFilter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\AirFilterResource as AirFilterResourceModel;

/**
 * @property AirFilter $resource
 */
class AirFilterResource extends JsonResource implements HasJsonSchema
{
    private AirFilterResourceModel $airFilterResource;

    public function __construct(AirFilter $resource)
    {
        parent::__construct($resource);
        $this->airFilterResource = new AirFilterResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->airFilterResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return AirFilterResourceModel::jsonSchema();
    }
}
