<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\InProgress\IndexRequest;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\BySearchString;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Order\Scopes\OrderedByStatus;
use App\Models\Scopes\NewestUpdated;
use App\Models\Substatus;
use Auth;

class InProgressController extends Controller
{
    public function index(IndexRequest $request)
    {
        $searchString = $request->get(RequestKeys::SEARCH_STRING);
        $supplier     = Auth::user()->supplier;
        $statuses     = array_merge(Substatus::STATUSES_APPROVED, Substatus::STATUSES_COMPLETED,
            Substatus::STATUSES_CANCELED);
        $query        = Order::query();
        if ($searchString) {
            $query->scoped(new BySearchString($searchString));
        }
        $orders = $query->scoped(new BySupplier($supplier))
            ->scoped(new ByLastSubstatuses($statuses))
            ->scoped(new OrderedByStatus())
            ->scoped(new NewestUpdated())
            ->with([
                'supplier',
                'itemOrders',
                'orderDelivery',
                'user.pubnubChannels' => function($query) use ($supplier) {
                    $query->scoped(new BySupplier($supplier));
                },
                'user.devices'        => function($query) {
                    $query->scoped(new NewestUpdated());
                },
            ])
            ->paginate();

        return BaseResource::collection($orders);
    }
}
