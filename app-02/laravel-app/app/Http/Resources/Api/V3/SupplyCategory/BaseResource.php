<?php

namespace App\Http\Resources\Api\V3\SupplyCategory;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupplyCategoryResource;
use App\Models\SupplyCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplyCategory $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplyCategoryResource $modelResource;

    public function __construct(SupplyCategory $resource)
    {
        parent::__construct($resource);
        $this->modelResource = new SupplyCategoryResource($resource);
    }

    public function toArray($request)
    {
        $supplyCategory              = $this->resource;
        $response                    = $this->modelResource->toArray($request);
        $response['has_descendants'] = $supplyCategory->children->isNotEmpty();

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = SupplyCategoryResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'has_descendants' => ['type' => ['boolean']],
            ],
            'required'   => [
                'has_descendants',
            ],
        ]);
    }
}
