<?php

namespace App\Http\Resources\Api\V3\Account\Supplier\Channel;

use App\Http\Resources\HasJsonSchema;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class OrderResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Order $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $order = $this->resource;

        return [
            'id'         => $order->getRouteKey(),
            'updated_at' => $order->updated_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'updated_at' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'updated_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
