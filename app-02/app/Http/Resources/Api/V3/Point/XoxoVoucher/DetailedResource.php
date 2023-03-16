<?php

namespace App\Http\Resources\Api\V3\Point\XoxoVoucher;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\XoxoVoucherResource;
use App\Models\XoxoVoucher;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property XoxoVoucher $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private XoxoVoucherResource $baseResource;

    public function __construct(XoxoVoucher $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new XoxoVoucherResource($resource);
    }

    public function toArray($request)
    {
        $resource                         = $this->baseResource->toArray($request);
        $resource['value_denominations']  = $this->resource->value_denominations;
        $resource['description']          = $this->resource->description;
        $resource['instructions']         = $this->resource->instructions;
        $resource['terms_and_conditions'] = $this->resource->terms_conditions;

        return $resource;
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(XoxoVoucherResource::jsonSchema(), [
            'properties' => [
                'value_denominations'  => ['type' => ['string']],
                'description'          => ['type' => ['string', 'null']],
                'instructions'         => ['type' => ['string', 'null']],
                'terms_and_conditions' => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'value_denominations',
                'description',
                'instructions',
                'terms_and_conditions',
            ],
        ]);
    }
}
