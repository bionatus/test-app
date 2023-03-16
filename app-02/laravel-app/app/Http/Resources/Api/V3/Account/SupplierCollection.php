<?php

namespace App\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V3\Supplier\BaseResource;
use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @property LengthAwarePaginator $resource
 */
class SupplierCollection extends ResourceCollection implements HasJsonSchema
{
    public function __construct(LengthAwarePaginator $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'data'          => BaseResource::collection($this->resource),
            'next_page_url' => $this->resource->nextPageUrl(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data'          => [
                    'type'  => 'array',
                    'items' => BaseResource::jsonSchema(),
                ],
                'next_page_url' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'data',
                'next_page_url',
            ],
            'additionalProperties' => false,
        ];
    }
}
