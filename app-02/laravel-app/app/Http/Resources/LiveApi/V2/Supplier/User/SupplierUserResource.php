<?php

namespace App\Http\Resources\LiveApi\V2\Supplier\User;

use App\Http\Resources\Models\SupplierUserResource as BaseSupplierUserResource;
use App\Models\SupplierUser;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplierUser $resource
 */
class SupplierUserResource extends JsonResource
{
    private BaseSupplierUserResource $baseSupplierUserResource;

    public function __construct(SupplierUser $resource)
    {
        parent::__construct($resource);
        $this->baseSupplierUserResource = new BaseSupplierUserResource($resource);
    }

    public function toArray($request): array
    {
        return $this->baseSupplierUserResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseSupplierUserResource::jsonSchema();
    }
}
