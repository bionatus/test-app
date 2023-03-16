<?php

namespace App\Http\Resources\Api\V3\Auth;

use App\Http\Resources\HasJsonSchema;
use App\Models\Phone;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Phone $resource
 */
abstract class CodeRequestedResource extends JsonResource implements HasJsonSchema
{
    const TYPE_SIGN_IN = 'sign_in';
    const TYPE_SIGN_UP = 'sign_up';

    public function __construct(Phone $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $phone = $this->resource;

        return [
            'phone'                     => $phone->fullNumber(),
            'next_request_available_at' => $phone->nextRequestAvailableAt(),
            'type'                      => $phone->isVerifiedAndAssigned() ? self::TYPE_SIGN_IN : self::TYPE_SIGN_UP,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'phone'                     => ['type' => ['string'], 'pattern' => '^\d+$'],
                'next_request_available_at' => ['type' => ['string']],
                'type'                      => ['type' => ['string']],
            ],
            'required'             => [
                'phone',
                'next_request_available_at',
                'type',
            ],
            'additionalProperties' => false,
        ];
    }
}
