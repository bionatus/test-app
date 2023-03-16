<?php

namespace App\Http\Resources\LiveApi\V1\Brand;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Brand $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private BrandResource $brandResource;

    public function __construct(Brand $resource)
    {
        parent::__construct($resource);

        $this->brandResource = new BrandResource($resource);
    }

    public function toArray($request)
    {
        return $this->brandResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BrandResource::jsonSchema();
    }
}
