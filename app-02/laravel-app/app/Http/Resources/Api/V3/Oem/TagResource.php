<?php

namespace App\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\Tag\ImageResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Media;
use App\Types\TaggableType;
use Arr;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TaggableType $resource
 */
class TagResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TaggableType $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var Media $firstMedia */
        $firstMedia = ($media = $this->resource->getMedia()) ? Arr::first($media) : null;

        return [
            'id'    => $this->resource->id,
            'name'  => $this->resource->name,
            'type'  => $this->resource->type,
            'image' => $firstMedia ? new ImageResource($firstMedia) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string']],
                'type'  => ['type' => ['string']],
                'image' => ImageResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'type',
            ],
            'additionalProperties' => false,
        ];
    }
}
