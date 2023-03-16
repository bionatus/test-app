<?php

namespace App\Http\Resources\Api\V3\Brand;

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

    public function toArray($request): array
    {
        $logo                     = $this->resource->logo;
        $response                 = $this->brandResource->toArray($request);
        $response['series_count'] = $this->resource->series_count;
        $response['image']        = (!empty($logo[0])) ? new ImageResource($logo) : null;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                               = BrandResource::jsonSchema();
        $schema['properties']['series_count'] = ['type' => ['number']];
        $schema['properties']['image']        = ImageResource::jsonSchema();
        $schema['required'][]                 = 'series_count';

        return $schema;
    }
}
