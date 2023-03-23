<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Constants\RequestKeys;
use App\Exceptions\CurriException;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\Delivery\UpdateRequest;
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
     * @throws CurriException|Throwable
     */
    public function update(UpdateRequest $request, Order $order)
    {
        $handler           = new OrderDeliveryHandler($order);
        $deliveryType      = $request->get(RequestKeys::TYPE);
        $dataOrderDelivery = [
            'type' => $deliveryType,
            'fee'  => $request->get(RequestKeys::FEE),
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

        if ($deliveryType === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $vehicleType = $request->get(RequestKeys::VEHICLE_TYPE);
            $result      = App::make(Curri::class)->getQuote($destinationAddress, $originAddress, $vehicleType);

            $dataTypeDelivery['vehicle_type'] = $vehicleType;
            $dataTypeDelivery['quote_id']     = $result['quoteId'];
            $dataOrderDelivery['fee']         = Money::toDollars($result['fee']);
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
