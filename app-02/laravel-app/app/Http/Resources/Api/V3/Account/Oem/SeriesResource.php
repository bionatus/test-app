<?php

namespace App\Http\Resources\Api\V3\Account\Oem;

use App\Http\Resources\Api\V3\Account\Oem\Series\BrandResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SeriesResource as BaseSeriesResource;
use App\Models\Series;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Series $resource
 */
class SeriesResource extends JsonResource implements HasJsonSchema
{
    private BaseSeriesResource $baseResource;

    public function __construct(Series $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseSeriesResource($resource);
    }

    public function toArray($request)
    {
        $response          = $this->baseResource->toArray($request);
        $response['brand'] = new BrandResource($this->resource->brand);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                        = BaseSeriesResource::jsonSchema();
        $schema['properties']['brand'] = BrandResource::jsonSchema();
        $schema['required'][]          = 'brand';

        return $schema;
    }
}
