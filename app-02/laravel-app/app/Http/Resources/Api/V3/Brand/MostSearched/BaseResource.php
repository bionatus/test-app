<?php

namespace App\Http\Resources\Api\V3\Brand\MostSearched;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\BrandResource as BaseBrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Brand $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private BaseBrandResource $baseBrandResource;

    public function __construct(Brand $resource)
    {
        parent::__construct($resource);
        $this->baseBrandResource = new BaseBrandResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseBrandResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseBrandResource::jsonSchema();
    }
}
