<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\XoxoRedemption;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property XoxoRedemption $resource
 */
class XoxoRedemptionResource extends JsonResource implements HasJsonSchema
{
    public function __construct(XoxoRedemption $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $xoxoRedemption = $this->resource;

        return [
            'id'                   => $xoxoRedemption->getRouteKey(),
            'redemption_code'      => $xoxoRedemption->redemption_code,
            'voucher_code'         => $xoxoRedemption->voucher_code,
            'name'                 => $xoxoRedemption->name,
            'image'                => $xoxoRedemption->image,
            'value_denomination'   => $xoxoRedemption->value_denomination,
            'amount_charged'       => $xoxoRedemption->amount_charged,
            'description'          => $xoxoRedemption->description,
            'instructions'         => $xoxoRedemption->instructions,
            'terms_and_conditions' => $xoxoRedemption->terms_conditions,
            'created_at'           => $xoxoRedemption->created_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                   => ['type' => ['string']],
                'redemption_code'      => ['type' => ['integer']],
                'voucher_code'         => ['type' => ['integer']],
                'name'                 => ['type' => ['string']],
                'image'                => ['type' => ['string']],
                'value_denomination'   => ['type' => ['integer']],
                'amount_charged'       => ['type' => ['integer']],
                'description'          => ['type' => ['string', 'null']],
                'instructions'         => ['type' => ['string', 'null']],
                'terms_and_conditions' => ['type' => ['string', 'null']],
                'created_at'           => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'redemption_code',
                'voucher_code',
                'name',
                'image',
                'value_denomination',
                'amount_charged',
                'description',
                'instructions',
                'terms_and_conditions',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
