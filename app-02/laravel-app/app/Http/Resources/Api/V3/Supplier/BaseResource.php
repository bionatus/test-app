<?php

namespace App\Http\Resources\Api\V3\Supplier;

use App\Http\Resources\Api\V3\Store\CountryResource;
use App\Http\Resources\Api\V3\Store\StateResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $supplierResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
        $this->supplierResource = new SupplierResource($resource);
    }

    public function toArray($request)
    {
        $response              = $this->supplierResource->toArray($request);
        $response['distance']  = $this->resource->distance;
        $response['preferred'] = !!$this->resource->preferred_supplier;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                            = SupplierResource::jsonSchema();
        $schema['properties']['state']     = StateResource::jsonSchema();
        $schema['properties']['country']   = CountryResource::jsonSchema();
        $schema['properties']['distance']  = ['type' => ['number', 'null']];
        $schema['properties']['preferred'] = ['type' => ['boolean']];
        $schema['required'][]              = 'preferred';
        $schema['required'][]              = 'distance';

        return $schema;
    }
}
