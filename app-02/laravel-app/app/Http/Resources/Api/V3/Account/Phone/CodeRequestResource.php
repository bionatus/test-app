<?php

namespace App\Http\Resources\Api\V3\Account\Phone;

use App\Http\Resources\HasJsonSchema;
use App\Models\Phone;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Phone $resource
 */
abstract class CodeRequestResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Phone $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $phone = $this->resource;

        return [
            'phone'                     => $phone->fullNumber(),
            'next_request_available_at' => $phone->nextRequestAvailableAt(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'phone'                     => ['type' => ['string'], 'pattern' => '^\d+$'],
                'next_request_available_at' => ['type' => ['string']],
            ],
            'required'             => [
                'phone',
                'next_request_available_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
