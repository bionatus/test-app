<?php

namespace App\Http\Resources\Api\V3\ModelType;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ModelTypeResource;
use App\Models\ModelType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ModelType $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private ModelTypeResource $modelTypeResource;

    public function __construct(ModelType $resource)
    {
        parent::__construct($resource);
        $this->modelTypeResource = new ModelTypeResource($resource);
    }

    public function toArray($request)
    {
        $response = $this->modelTypeResource->toArray($request);
        $response['oems_count'] = $this->resource->oemsCount();

        return $response;
    }

    public static function jsonSchema(): array
    {
        return ModelTypeResource::jsonSchema();
    }
}
