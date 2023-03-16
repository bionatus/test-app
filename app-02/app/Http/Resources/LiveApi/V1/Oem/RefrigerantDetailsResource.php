<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class RefrigerantDetailsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'refrigerant'        => $this->resource->refrigerant,
            'original_charge_oz' => $this->resource->original_charge_oz,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'refrigerant'        => ['type' => ['string', 'null']],
                'original_charge_oz' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'refrigerant',
                'original_charge_oz',
            ],
            'additionalProperties' => false,
        ];
    }
}
