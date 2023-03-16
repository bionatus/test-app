<?php

namespace App\Http\Resources\Api\V3\Account\BulkSupplier;

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
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SupplierResource::jsonSchema();
    }
}
