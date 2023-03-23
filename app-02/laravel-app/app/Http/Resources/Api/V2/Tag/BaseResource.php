<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use App\Types\TaggableType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TaggableType $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TaggableType $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->resource->toArray();
    }

    public function toArrayWithAdditionalData(array $data = []): array
    {
        return array_merge($this->resolve(), $data);
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'     => ['type' => ['string']],
                'name'   => ['type' => ['string']],
                'type'   => ['type' => ['string']],
                'images' => [
                    'type'  => ['array'],
                    'items' => ImageCollection::jsonSchema(),
                ],
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
