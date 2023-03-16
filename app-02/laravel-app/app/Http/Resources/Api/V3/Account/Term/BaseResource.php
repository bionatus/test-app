<?php

namespace App\Http\Resources\Api\V3\Account\Term;

use App\Http\Resources\HasJsonSchema;
use App\Models\TermUser;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TermUser $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TermUser $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'    => $this->resource->getRouteKey(),
            'title' => $this->resource->term->title,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['integer']],
                'title' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'title',
            ],
            'additionalProperties' => false,
        ];
    }
}
