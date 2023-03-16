<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class OilDetailsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'oil_type'   => $this->resource->oil_type,
            'oil_amt_oz' => $this->resource->oil_amt_oz,
            'oil_notes'  => $this->resource->oil_notes,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'oil_type'   => ['type' => ['string', 'null']],
                'oil_amt_oz' => ['type' => ['string', 'null']],
                'oil_notes'  => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'oil_type',
                'oil_amt_oz',
                'oil_notes',
            ],
            'additionalProperties' => false,
        ];
    }
}
