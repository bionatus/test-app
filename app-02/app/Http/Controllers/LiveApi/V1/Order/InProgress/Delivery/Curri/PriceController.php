<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri;

use App;
use App\Constants\RequestKeys;
use App\Exceptions\CurriException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\Curri\Price\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Order\InProgress\Delivery\Curri\Price\BaseResource;
use App\Models\Address;
use App\Models\Order;
use App\Services\Curri\Curri;
use App\Types\Money;
use Illuminate\Support\Collection;

class PriceController extends Controller
{
    /**
     * @throws CurriException
     */
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $delivery    = $order->orderDelivery;
        $supplier    = $order->supplier;
        $vehicleType = $request->get(RequestKeys::VEHICLE_TYPE);
        $collection  = $request->get(RequestKeys::USE_STORE_ADDRESS) ? Collection::make($supplier->toArray()) : $request->collect();

        $collection->prepend($collection->get(RequestKeys::ADDRESS), 'address_1');
        $collection->forget(RequestKeys::ADDRESS);

        $address            = $collection->only([
            'address_1',
            RequestKeys::ADDRESS_2,
            RequestKeys::CITY,
            RequestKeys::COUNTRY,
            RequestKeys::STATE,
            RequestKeys::ZIP_CODE,
        ]);
        $originAddress      = new Address($address->toArray());
        $destinationAddress = $delivery->deliverable->destinationAddress;

        $result = App::make(Curri::class)->getQuote($destinationAddress, $originAddress, $vehicleType);

        $delivery->fee = Money::toDollars($result['fee']);

        return new BaseResource($delivery);
    }
}
