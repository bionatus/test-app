<?php

namespace App\Http\Resources\BasecampApi\V1\User\Supplier;

use App\Http\Resources\Models\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HasJsonSchema;

/** @property Supplier $resource */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $supplierResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);

        $this->supplierResource = new SupplierResource($resource);
    }

    public function toArray($request): array
    {
        $supplier     = $this->resource;
        $baseResource = $this->supplierResource->toArray($request);

        return array_merge_recursive($baseResource, [
            'verified' => !!$supplier->verified_at,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(SupplierResource::jsonSchema(), [
            'properties' => [
                'verified' => ['type' => ['boolean']],
            ],
            'required'   => [
                'verified',
            ],
        ]);
    }
}
