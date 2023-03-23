<?php

namespace App\Http\Resources\LiveApi\V1\Supplier\BulkHour;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupplierHourResource;
use App\Models\SupplierHour;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplierHour $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplierHourResource $storeHourResource;

    public function __construct(SupplierHour $resource)
    {
        $this->storeHourResource = new SupplierHourResource($resource);
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->storeHourResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SupplierHourResource::jsonSchema();
    }
}
