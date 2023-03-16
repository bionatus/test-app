<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CurriDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CurriDelivery $resource
 */
class CurriDeliveryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(CurriDelivery $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $curriDelivery = $this->resource;
        $originAddress = $curriDelivery->originAddress;
        $destinationAddress = $curriDelivery->destinationAddress;

        return [
            'supplier_confirmed_at' => $curriDelivery->supplier_confirmed_at,
            'quote_id'              => $curriDelivery->quote_id,
            'book_id'               => $curriDelivery->book_id,
            'vehicle_type'          => $curriDelivery->vehicle_type,
            'origin_address'        => ($originAddress) ? new AddressResource($originAddress) : null,
            'destination_address'   => ($destinationAddress) ?new AddressResource($destinationAddress) : null,
            'tracking_url'          => $curriDelivery->tracking_url,
            'status'                => $curriDelivery->status,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'supplier_confirmed_at' => ['type' => ['string', 'null']],
                'quote_id'              => ['type' => ['string', 'null']],
                'book_id'               => ['type' => ['string', 'null']],
                'vehicle_type'          => ['type' => ['string']],
                'origin_address'        => ['type' => ['object', 'null']],
                'destination_address'   => ['type' => ['object', 'null']],
                'tracking_url'          => ['type' => ['string', 'null']],
                'status'                => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'supplier_confirmed_at',
                'quote_id',
                'book_id',
                'vehicle_type',
                'origin_address',
                'destination_address',
                'tracking_url',
                'status',
            ],
            'additionalProperties' => false,
        ];
    }
}
