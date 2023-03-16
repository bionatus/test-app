<?php

namespace App\Http\Controllers\LiveApi\V2;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\IndexRequest;
use App\Http\Requests\LiveApi\V2\Order\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\BaseResource;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\CurriDelivery;
use App\Models\Item;
use App\Models\Item\Scopes\ByTypes;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\Order;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Order\Scopes\PriceAndAvailabilityRequests;
use App\Models\Order\Scopes\WillCallAndApprovedOrders;
use App\Models\Pickup;
use App\Models\Scopes\NewestUpdated;
use App\Models\ShipmentDelivery;
use App\Models\Substatus;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(IndexRequest $request)
    {
        $supplier = Auth::user()->supplier;

        $query = Order::query();
        $query->withCount('items as total_line_items');
        $query->withExists([
            'items as extra_items_added_later' => function(Builder $query) {
                $query->scoped(new ByInitialRequest(false));
            },
        ]);
        $query->with('items', function(BelongsToMany $query) {
            $query->scoped(new ByTypes([Item::TYPE_PART, Item::TYPE_SUPPLY]))->take(3);
        });
        $query->with('user.supplierUsers', function(HasMany $query) use ($supplier) {
            $query->scoped(new BySupplier($supplier));
        });
        $query->with(['lastOrderStaff', 'supplier', 'company']);

        $query->scoped(new BySupplier($supplier))->scoped(new NewestUpdated());

        if ($request->get(RequestKeys::TYPE) === ORDER::TYPE_ORDER_LIST_AVAILABILITY) {
            $query->scoped(new PriceAndAvailabilityRequests());
        }

        if ($request->get(RequestKeys::TYPE) === ORDER::TYPE_ORDER_LIST_APPROVED) {
            $query->scoped(new WillCallAndApprovedOrders());
        }

        $orders = $query->paginate();

        return BaseResource::collection($orders);
    }

    public function show(Order $order)
    {
        return new DetailedResource($order);
    }

    /**
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, Order $order)
    {
        $updatedOrder = DB::transaction(function() use ($request, $order) {
            $order->total      = $request->get(RequestKeys::TOTAL);
            $order->bid_number = $request->get(RequestKeys::BID_NUMBER);
            $order->note       = $request->get(RequestKeys::NOTE);
            $order->save();

            if ($order->lastSubStatusIsAnyOf([Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED])) {
                /** @var CurriDelivery|Pickup|ShipmentDelivery $delivery */
                $delivery = $order->orderDelivery->deliverable;
                $handler  = $delivery->createSubstatusHandler($order);
                $order    = $handler->processPendingApprovalQuoteNeeded($order);
            }

            return $order;
        });

        return (new DetailedResource($updatedOrder))->response()->setStatusCode(Response::HTTP_OK);
    }
}
