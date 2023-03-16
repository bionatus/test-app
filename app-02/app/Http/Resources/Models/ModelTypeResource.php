<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\ModelType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ModelType $resource
 */
class ModelTypeResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ModelType $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $modelType = $this->resource;
        $image     = $modelType->getMedia(MediaCollectionNames::IMAGES)->first();

        return [
            'id'    => $modelType->getRouteKey(),
            'name'  => $modelType->name,
            'image' => $image ? new ImageResource($image) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties' => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string', 'null']],
                'image' => ImageResource::jsonSchema(true),
            ],
            'required'   => [
                'id',
                'name',
                'image',
            ],
        ];
    }
}
