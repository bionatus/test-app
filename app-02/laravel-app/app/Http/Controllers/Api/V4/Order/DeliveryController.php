<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Constants\RequestKeys;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\Delivery\StoreRequest;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Scopes\ByRouteKey;
use App\Models\ShipmentDeliveryPreference;

class DeliveryController extends Controller
{
    public function store(StoreRequest $request, Order $order)
    {
        $date         = $request->get(RequestKeys::REQUESTED_DATE);
        $startTime    = $request->get(RequestKeys::REQUESTED_START_TIME);
        $endTime      = $request->get(RequestKeys::REQUESTED_END_TIME);
        $deliveryType = $request->get(RequestKeys::TYPE);
        $isNeededNow  = $request->get(RequestKeys::IS_NEEDED_NOW);

        $dataTypeDelivery = [];
        if ($deliveryType == OrderDelivery::TYPE_SHIPMENT_DELIVERY) {
            $shipmentPreference = ShipmentDeliveryPreference::scoped(new ByRouteKey($request->get(RequestKeys::SHIPMENT_PREFERENCE)))
                ->first();
            $dataTypeDelivery   = ['shipment_delivery_preference_id' => $shipmentPreference->getKey()];
        }

        $dataOrderDelivery = [
            'type'                 => $deliveryType,
            'requested_date'       => $date,
            'requested_start_time' => $startTime,
            'requested_end_time'   => $endTime,
            'date'                 => $date,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'is_needed_now'        => $isNeededNow,
        ];

        $handler          = new OrderDeliveryHandler($order);
        $newOrderDelivery = $handler->createOrUpdateDelivery($dataOrderDelivery);
        $handler->createOrUpdateDeliveryType(null, null, $dataTypeDelivery);
        $orderDelivery = $newOrderDelivery->refresh();

        return new OrderDeliveryResource($orderDelivery);
    }
}
