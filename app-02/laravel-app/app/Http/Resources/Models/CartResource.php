<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Cart;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Cart $resource
 */
class CartResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Cart $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'created_at' => $this->resource->created_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'created_at' => ['type' => ['string']],
            ],
            'required'             => [
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
