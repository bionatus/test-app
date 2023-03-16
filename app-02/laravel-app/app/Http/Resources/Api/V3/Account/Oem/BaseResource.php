<?php

namespace App\Http\Resources\Api\V3\Account\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OemResource $oemResource;

    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
        $this->oemResource = new OemResource($resource);
    }

    public function toArray($request)
    {
        $series = $this->resource->series;

        $resource           = $this->oemResource->toArray($request);
        $resource['series'] = new SeriesResource($series);

        return $resource;
    }

    public static function jsonSchema(): array
    {
        $schema                         = OemResource::jsonSchema();
        $schema['properties']['series'] = SeriesResource::jsonSchema();
        $schema['required'][]           = 'series';

        return $schema;
    }
}
