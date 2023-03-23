<?php

namespace App\Http\Resources\LiveApi\V1\Supplier;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\BrandResource as BaseBrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource implements HasJsonSchema
{
    private BaseBrandResource $brandResource;

    public function __construct(Brand $resource)
    {
        parent::__construct($resource);

        $this->brandResource = new BaseBrandResource($resource);
    }

    public function toArray($request)
    {
        return $this->brandResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        $base         = BaseBrandResource::jsonSchema();
        $base['type'] = ['object', 'array'];

        return $base;
    }
}
