<?php

namespace App\Http\Resources\Api\V3\Account\Phone\Verify;

use App\Http\Resources\HasJsonSchema;
use App\Models\Phone;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Phone $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id' => $this->resource->fullNumber(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
            ],
            'additionalProperties' => false,
        ];
    }
}
