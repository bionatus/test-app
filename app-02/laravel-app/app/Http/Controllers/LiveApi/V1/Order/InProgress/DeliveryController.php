<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress;

use App;
use App\Constants\RequestKeys;
use App\Events\Order\DeliveryEtaUpdated;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\UpdateRequest;
use App\Http\Resources\LiveApi\V1\Order\Delivery\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Services\Curri\Curri;
use App\Types\Money;
use DB;
use Illuminate\Support\Collection;
use Throwable;

class DeliveryController extends Controller
{
    /**
     * @throws Throwable
     */
    public function update(UpdateRequest $request, Order $order)
    {
        $handler = new OrderDeliveryHandler($order);

        $dataOrderDelivery = [
            'date'       => $request->input(RequestKeys::DATE),
            'start_time' => $request->input(RequestKeys::START_TIME),
            'end_time'   => $request->input(RequestKeys::END_TIME),
            'fee'        => $request->get(RequestKeys::FEE),
        ];

        $dataTypeDelivery    = [];
        $originAddress       = $destinationAddress = null;
        $dataOriginAddresses = $this->getOriginAddressData($request, $order);
        if ($dataOriginAddresses) {
            $originAddress = $handler->createOrUpdateOriginAddress($dataOriginAddresses);
        }

        if ($handler->getOldDeliveryType()->hasDestinationAddress()) {
            $destinationAddress = $handler->getOldDeliveryType()->destinationAddress;
        }

        if ($handler->getType() === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $vehicleType = $handler->getOldDeliveryType()->vehicle_type;
            $result      = App::make(Curri::class)->getQuote($destinationAddress, $originAddress, $vehicleType);

            $dataTypeDelivery['quote_id'] = $result['quoteId'];
            $dataOrderDelivery['fee']     = Money::toDollars($result['fee']);
        }

        $orderDelivery = DB::transaction(function() use (
            $handler,
            $dataOrderDelivery,
            $dataTypeDelivery,
            $originAddress,
            $destinationAddress
        ) {
            $newOrderDelivery = $handler->createOrUpdateDelivery($dataOrderDelivery);

            $handler->createOrUpdateDeliveryType($destinationAddress, $originAddress, $dataTypeDelivery);

            return $newOrderDelivery->refresh();
        });

        if ($orderDelivery->wasChanged(['date', 'start_time', 'end_time'])) {
            DeliveryEtaUpdated::dispatch($order);
        }

        return new BaseResource($orderDelivery);
    }

    private function getOriginAddressData(UpdateRequest $request, Order $order)
    {
        $useStoreAddress = $request->get(RequestKeys::USE_STORE_ADDRESS);
        if (null === $useStoreAddress) {
            return null;
        }

        $collection = $useStoreAddress ? Collection::make($order->supplier->toArray()) : $request->collect();
        $collection->prepend($collection->get(RequestKeys::ADDRESS), 'address_1');
        $collection->forget(RequestKeys::ADDRESS);

        return $collection->only([
            'address_1',
            RequestKeys::ADDRESS_2,
            RequestKeys::CITY,
            RequestKeys::COUNTRY,
            RequestKeys::STATE,
            RequestKeys::ZIP_CODE,
        ])->toArray();
    }
}
