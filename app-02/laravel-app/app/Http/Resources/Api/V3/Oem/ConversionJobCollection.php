<?php

namespace App\Http\Resources\Api\V3\Oem;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @property LengthAwarePaginator $resource
 */
class ConversionJobCollection extends ResourceCollection implements HasJsonSchema
{
    public function __construct(LengthAwarePaginator $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'data'           => ConversionJobResource::collection($this->resource),
            'has_more_pages' => $this->resource->hasMorePages(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data'           => [
                    'type'  => 'array',
                    'items' => ConversionJobResource::jsonSchema(),
                ],
                'has_more_pages' => ['type' => ['boolean']],
            ],
            'required'             => [
                'data',
                'has_more_pages',
            ],
            'additionalProperties' => false,
        ];
    }
}
