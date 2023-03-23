<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\XoxoVoucher;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property XoxoVoucher $resource
 */
class XoxoVoucherResource extends JsonResource implements HasJsonSchema
{
    public function __construct(XoxoVoucher $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $voucher = $this->resource;

        return [
            'id'    => $voucher->getRouteKey(),
            'name'  => $voucher->name,
            'image' => $voucher->image,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['integer']],
                'name'  => ['type' => ['string']],
                'image' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
