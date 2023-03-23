<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\AuthenticationCode;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AuthenticationCode $resource
 */
class AuthenticationCodeResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AuthenticationCode $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'code' => $this->resource->code,
            'type' => $this->resource->type,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'code' => ['type' => ['string']],
                'type' => ['type' => ['string']],
            ],
            'required'             => [
                'code',
                'type',
            ],
            'additionalProperties' => false,
        ];
    }
}
