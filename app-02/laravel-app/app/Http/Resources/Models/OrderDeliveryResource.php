<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CurriDelivery;
use App\Models\OrderDelivery;
use App\Models\OtherDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\WarehouseDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OrderDelivery $resource
 */
class OrderDeliveryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(OrderDelivery $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $orderDelivery = $this->resource;
        /** @var Pickup|CurriDelivery|OtherDelivery|ShipmentDelivery|WarehouseDelivery $deliverable */
        $deliverable = $this->resource->deliverable;

        $date               = $this->resource->date;
        $existStartTime     = $this->resource->start_time;
        $existEndTime       = $this->resource->end_time;
        $requestedDate      = $this->resource->requested_date;
        $requestedStartTime = $this->resource->requested_start_time;
        $requestedEndTime   = $this->resource->requested_end_time;

        $response = [
            'requested_date'       => $requestedDate ? $requestedDate->format('Y-m-d') : null,
            'requested_start_time' => $requestedStartTime ? $requestedStartTime->format('H:i') : null,
            'requested_end_time'   => $requestedEndTime ? $requestedEndTime->format('H:i') : null,
            'date'                 => $date ? $date->format('Y-m-d') : null,
            'start_time'           => $existStartTime ? $existStartTime->format('H:i') : null,
            'end_time'             => $existEndTime ? $existEndTime->format('H:i') : null,
            'fee'                  => $this->resource->fee,
            'note'                 => $this->resource->note,
            'type'                 => $this->resource->type,
            'is_needed_now'        => $this->resource->isNeededNow(),
        ];

        if ($orderDelivery->isPickup()) {
            $response['info'] = null;
        }

        if ($orderDelivery->isCurriDelivery()) {
            $response['info'] = $deliverable ? new CurriDeliveryResource($deliverable) : null;
        }

        if ($orderDelivery->isOtherDelivery()) {
            $response['info'] = $deliverable ? new OtherDeliveryResource($deliverable) : null;
        }

        if ($orderDelivery->isShipmentDelivery()) {
            $response['info'] = $deliverable ? new ShipmentDeliveryResource($deliverable) : null;
        }

        if ($orderDelivery->isWarehouseDelivery()) {
            $response['info'] = $deliverable ? new WarehouseDeliveryResource($deliverable) : null;
        }

        return $response;
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'requested_date'       => ['type' => ['string', 'null']],
                'requested_start_time' => ['type' => ['string', 'null']],
                'requested_end_time'   => ['type' => ['string', 'null']],
                'date'                 => ['type' => ['string', 'null']],
                'start_time'           => ['type' => ['string', 'null']],
                'end_time'             => ['type' => ['string', 'null']],
                'fee'                  => ['type' => ['number', 'null']],
                'note'                 => ['type' => ['string', 'null']],
                'type'                 => ['type' => ['string']],
                'info'                 => [
                    'anyOf' => [
                        ['type' => ['null']],
                        PickupResource::jsonSchema(),
                        CurriDeliveryResource::jsonSchema(),
                        OtherDeliveryResource::jsonSchema(),
                        ShipmentDeliveryResource::jsonSchema(),
                        WarehouseDeliveryResource::jsonSchema(),
                    ],
                ],
                'is_needed_now'        => ['type' => ['boolean']],
            ],
            'required'             => [
                'requested_date',
                'requested_start_time',
                'requested_end_time',
                'date',
                'start_time',
                'end_time',
                'fee',
                'note',
                'type',
                'info',
                'is_needed_now',
            ],
            'additionalProperties' => false,
        ];
    }
}
