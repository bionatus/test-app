<?php

namespace App\Http\Resources\Api\V3\Account\Supplier;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $baseResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new SupplierResource($resource);
    }

    public function toArray($request)
    {
        $response              = $this->baseResource->toArray($request);
        $response['preferred'] = !!$this->resource->preferred_supplier;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                            = SupplierResource::jsonSchema();
        $schema['properties']['preferred'] = ['type' => ['boolean']];
        $schema['required'][]              = 'preferred';

        return $schema;
    }
}
