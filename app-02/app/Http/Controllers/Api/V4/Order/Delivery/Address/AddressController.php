<?php

namespace App\Http\Controllers\Api\V4\Order\Delivery\Address;

use App;
use App\Constants\RequestKeys;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\Delivery\Address\StoreRequest;
use App\Http\Resources\Api\V4\Order\Delivery\BaseResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Services\Curri\Curri;
use App\Types\Money;
use DB;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AddressController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request, Order $order)
    {
        $orderDelivery = $order->orderDelivery;
        /** @var Pickup|CurriDelivery|ShipmentDelivery $deliverable */
        $deliverable = $orderDelivery->deliverable;

        DB::transaction(function() use ($request, $orderDelivery, $deliverable, $order) {
            /** @var Address $destinationAddress */
            $destinationAddress = $deliverable->destinationAddress()
                ->updateOrCreate([Address::keyName() => $deliverable->destination_address_id], [
                    'address_1' => $request->get(RequestKeys::DESTINATION_ADDRESS_1),
                    'address_2' => $request->get(RequestKeys::DESTINATION_ADDRESS_2),
                    'country'   => $request->get(RequestKeys::DESTINATION_COUNTRY),
                    'state'     => $request->get(RequestKeys::DESTINATION_STATE),
                    'zip_code'  => $request->get(RequestKeys::DESTINATION_ZIP_CODE),
                    'city'      => $request->get(RequestKeys::DESTINATION_CITY),
                ]);

            if ($orderDelivery->isCurriDelivery()) {
                $supplier   = $order->supplier;
                $originData = Collection::make($supplier->toArray());
                $originData->prepend($originData->get(RequestKeys::ADDRESS), 'address_1');
                $originData->forget(RequestKeys::ADDRESS);
                $originData = $originData->only([
                    'address_1',
                    'address_2',
                    'city',
                    'country',
                    'state',
                    'zip_code',
                ])->toArray();

                $handler       = new OrderDeliveryHandler($order);
                $originAddress = $handler->createOrUpdateOriginAddress($originData);
                $vehicleType   = CurriDelivery::VEHICLE_TYPE_CAR;
                $result        = App::make(Curri::class)->getQuote($destinationAddress, $originAddress, $vehicleType);

                $deliverable->origin_address_id = $originAddress->getKey();
                $deliverable->quote_id          = $result['quoteId'];
                $deliverable->vehicle_type      = $vehicleType;

                $orderDelivery->fee = Money::toDollars($result['fee']);
            }

            $deliverable->destination_address_id = $destinationAddress->getKey();
            $deliverable->save();

            $orderDelivery->note = $request->get(RequestKeys::NOTE);
            $orderDelivery->save();
        });

        return (new BaseResource($orderDelivery))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
