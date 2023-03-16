<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\SupportCallCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupportCallCategory $resource
 */
class SupportCallCategoryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupportCallCategory $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $supportCallCategory = $this->resource;
        $image               = $supportCallCategory->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'          => $supportCallCategory->getRouteKey(),
            'name'        => $supportCallCategory->name,
            'description' => $supportCallCategory->description,
            'phone'       => $supportCallCategory->phone,
            'image'       => $image ? new ImageResource($image) : null,
            'instruments' => InstrumentResource::collection($supportCallCategory->instruments),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'name'        => ['type' => ['string']],
                'description' => ['type' => ['string', 'null']],
                'phone'       => ['type' => ['string']],
                'image'       => ImageResource::jsonSchema(true),
                'instruments' => [
                    'type'  => ['array'],
                    'items' => InstrumentResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'id',
                'name',
                'description',
                'phone',
                'image',
                'instruments',
            ],
            'additionalProperties' => false,
        ];
    }
}
