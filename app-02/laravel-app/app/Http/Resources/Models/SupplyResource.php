<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Supply;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supply $resource
 */
class SupplyResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supply $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $supply = $this->resource;
        $media  = $supply->getCategoryMedia();

        return [
            'id'            => $supply->item->getRouteKey(),
            'name'          => $supply->name,
            'internal_name' => $supply->internal_name,
            'sort'          => $supply->sort,
            'image'         => $media ? new ImageResource($media) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'            => ['type' => ['string']],
                'name'          => ['type' => ['string']],
                'internal_name' => ['type' => ['string', 'null']],
                'sort'          => ['type' => ['integer', 'null']],
                'image'         => ImageResource::jsonSchema(true),
            ],
            'required'             => [
                'id',
                'name',
                'internal_name',
                'sort',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
