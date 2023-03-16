<?php

namespace App\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\HasJsonSchema;
use App\Models\Tool;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Tool $resource
 */
class ToolResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Tool $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $tool = $this->resource;

        return [
            'id'     => $tool->getRouteKey(),
            'name'   => $tool->name,
            'images' => new ImageCollection($tool->getMedia(MediaCollectionNames::IMAGES)),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'     => ['type' => ['string']],
                'name'   => ['type' => ['string']],
                'images' => ImageCollection::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'images',
            ],
            'additionalProperties' => false,
        ];
    }
}
