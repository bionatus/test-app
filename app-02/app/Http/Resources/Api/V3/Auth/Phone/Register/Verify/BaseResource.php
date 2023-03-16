<?php

namespace App\Http\Resources\Api\V3\Auth\Phone\Register\Verify;

use App\Models\Phone;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Phone $resource
 */
class BaseResource extends JsonResource
{
    private string $token;

    public function __construct(Phone $resource, string $token)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray($request)
    {
        return [
            'id'    => $this->resource->fullNumber(),
            'token' => $this->token,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'token' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'token',
            ],
            'additionalProperties' => true,
        ];
    }
}
