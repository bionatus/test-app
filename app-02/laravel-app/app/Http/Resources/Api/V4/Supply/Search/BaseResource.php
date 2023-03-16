<?php

namespace App\Http\Resources\Api\V4\Supply\Search;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupplyCategoryResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\Supply;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supply $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplyResource $supplyResource;

    public function __construct(Supply $resource)
    {
        parent::__construct($resource);

        $this->supplyResource = new SupplyResource($resource);
    }

    public function toArray($request)
    {
        $supplyResource             = $this->supplyResource->toArray($request);
        $supplyResource['category'] = new SupplyCategoryResource($this->resource->supplyCategory);

        unset($supplyResource['sort']);

        return $supplyResource;
    }

    public static function jsonSchema(): array
    {
        $schema                           = SupplyResource::jsonSchema();
        $schema['properties']['category'] = SupplyCategoryResource::jsonSchema();
        unset($schema['properties']['sort']);

        $schema['required'] = [
            'id',
            'name',
            'internal_name',
            'image',
            'category',
        ];

        return $schema;
    }
}
