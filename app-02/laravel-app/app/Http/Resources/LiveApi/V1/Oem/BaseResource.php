<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

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

        return array_replace_recursive($this->oemResource->toArray($request), [
            'series' => new SeriesResource($series),
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(OemResource::jsonSchema(), [
            'properties' => [
                'series' => SeriesResource::jsonSchema(),
            ],
            'required'   => [
                'series',
            ],
        ]);
    }
}
