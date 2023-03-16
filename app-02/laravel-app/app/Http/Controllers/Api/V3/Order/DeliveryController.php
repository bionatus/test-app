<?php

namespace App\Http\Controllers\Api\V3\Order;

use App;
use App\Constants\RequestKeys;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Order\Delivery\UpdateRequest;
use App\Http\Resources\Api\V3\Order\Delivery\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use DB;

class DeliveryController extends Controller
{
    public function update(UpdateRequest $request, Order $order)
    {
        $date         = $request->get(RequestKeys::REQUESTED_DATE);
        $startTime    = $request->get(RequestKeys::REQUESTED_START_TIME);
        $endTime      = $request->get(RequestKeys::REQUESTED_END_TIME);
        $deliveryType = $request->get(RequestKeys::TYPE);

        $isCurriDeliveryEnabled = true;

        if ($deliveryType == OrderDelivery::TYPE_CURRI_DELIVERY) {
            $isCurriDeliveryEnabled = $order->supplier->isCurriDeliveryEnabled();
        }

        $dataOrderDelivery = [
            'type'                 => $isCurriDeliveryEnabled ? $deliveryType : OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
            'requested_date'       => $date,
            'requested_start_time' => $startTime,
            'requested_end_time'   => $endTime,
            'date'                 => $date,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'note'                 => $request->get(RequestKeys::NOTE),
        ];

        $dataAddresses = [
            'address_1' => $request->get(RequestKeys::DESTINATION_ADDRESS_1),
            'address_2' => $request->get(RequestKeys::DESTINATION_ADDRESS_2),
            'country'   => $request->get(RequestKeys::DESTINATION_COUNTRY),
            'state'     => $request->get(RequestKeys::DESTINATION_STATE),
            'city'      => $request->get(RequestKeys::DESTINATION_CITY),
            'zip_code'  => $request->get(RequestKeys::DESTINATION_ZIP_CODE),
        ];

        $orderDelivery = DB::transaction(function() use ($order, $dataOrderDelivery, $dataAddresses) {
            $handler = new OrderDeliveryHandler($order);

            $newOrderDelivery   = $handler->createOrUpdateDelivery($dataOrderDelivery);
            $destinationAddress = null;
            if ($newOrderDelivery->isDelivery()) {
                $destinationAddress = $handler->createOrUpdateDestinationAddress($dataAddresses);
            }
            $handler->createOrUpdateDeliveryType($destinationAddress);

            return $newOrderDelivery->refresh();
        });

        return new BaseResource($orderDelivery);
    }
}
