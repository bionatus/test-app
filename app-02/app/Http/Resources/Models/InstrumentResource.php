<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\Instrument;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Instrument $resource
 */
class InstrumentResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Instrument $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $instrument = $this->resource;
        $image      = $instrument->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'    => $instrument->getRouteKey(),
            'name'  => $instrument->name,
            'image' => $image ? new ImageResource($image) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string']],
                'image' => ImageResource::jsonSchema(true),
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
