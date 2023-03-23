<?php

namespace App\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Oem\SeriesResource;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Oem $resource */
class OemResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'          => $this->resource->getRouteKey(),
            'model'       => $this->resource->model,
            'model_notes' => $this->resource->model_notes,
            'logo'        => $this->resource->logo,
            'image'       => $this->resource->unit_image,
            'series'      => new SeriesResource($this->resource->series),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'model'       => ['type' => ['string']],
                'model_notes' => ['type' => ['string', 'null']],
                'logo'        => ['type' => ['string']],
                'image'       => ['type' => ['string', 'null']],
                'series'      => SeriesResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'model',
                'model_notes',
                'logo',
                'image',
                'series',
            ],
            'additionalProperties' => false,
        ];
    }
}
