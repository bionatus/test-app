<?php

namespace App\Http\Resources\Api\V2\Twilio\Token;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;
use Twilio\Jwt\AccessToken;

/**
 * @property AccessToken $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AccessToken $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'token' => $this->resource->toJWT(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'token' => ['type' => ['string']],
            ],
            'required'             => [
                'token',
            ],
            'additionalProperties' => false,
        ];
    }
}
