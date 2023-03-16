<?php

namespace App\Http\Resources\Api\V3\Account\Oem\Series;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Brand\ImageResource;
use App\Http\Resources\Models\BrandResource as BaseBrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Brand $resource
 */
class BrandResource extends JsonResource implements HasJsonSchema
{
    private BaseBrandResource $baseBrandResource;

    public function __construct(Brand $resource)
    {
        parent::__construct($resource);
        $this->baseBrandResource = new BaseBrandResource($resource);
    }

    public function toArray($request)
    {
        $logo              = $this->resource->logo;
        $response          = $this->baseBrandResource->toArray($request);
        $response['image'] = (!empty($logo[0])) ? new ImageResource($logo) : null;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                        = BaseBrandResource::jsonSchema();
        $schema['properties']['image'] = ImageResource::jsonSchema(true);

        return $schema;
    }
}
