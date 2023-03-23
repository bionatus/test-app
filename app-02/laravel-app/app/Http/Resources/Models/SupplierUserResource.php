<?php

namespace App\Http\Resources\Models;

use App\Models\SupplierUser;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplierUser $resource
 */
class SupplierUserResource extends JsonResource
{
    public function __construct(SupplierUser $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'status'        => $this->resource->status,
            'customer_tier' => $this->resource->customer_tier,
            'cash_buyer'    => $this->resource->cash_buyer,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'status'        => ['type' => ['string']],
                'customer_tier' => ['type' => ['string', 'null']],
                'cash_buyer'    => ['type' => ['boolean']],
            ],
            'required'             => [
                'status',
                'customer_tier',
                'cash_buyer',
            ],
            'additionalProperties' => false,
        ];
    }
}
