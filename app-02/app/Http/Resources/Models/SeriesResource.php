<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\Models\Brand\Series\ImageResource;
use App\Models\Series;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Series $resource
 */
class SeriesResource extends JsonResource
{
    public function __construct(Series $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $image = $this->resource->image;

        return [
            'id'    => $this->resource->getRouteKey(),
            'name'  => $this->resource->name,
            'image' => $image ? new ImageResource($image) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['integer']],
                'name'  => ['type' => ['string']],
                'image' => ImageResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
