<?php

namespace App\Http\Resources\Api\V3\Supply;

use App\Http\Resources\HasJsonSchema;
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
        return $this->supplyResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SupplyResource::jsonSchema();
    }
}
