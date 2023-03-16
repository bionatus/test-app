<?php

namespace App\Http\Controllers\LiveApi\V2\Order\InProgress\ItemOrder;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItem\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\ItemOrder\Scopes\IsUserCustomItemOrSupply;
use App\Models\Order;
use App\Models\Scopes\ByUuid;
use DB;

class ExtraItemController extends Controller
{
    public function index(Order $order)
    {
        $items = $order->itemOrders()
            ->with('item')
            ->scoped(new ByInitialRequest(false))
            ->scoped(new IsUserCustomItemOrSupply())
            ->paginate(1000);

        return BaseResource::collection($items);
    }

    /**
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, Order $order)
    {
        DB::transaction(function() use ($request) {
            foreach ($request->get(RequestKeys::ITEMS) as $itemOrderInfo) {
                /** @var ItemOrder $itemOrder */
                $itemOrder = ItemOrder::scoped(new ByUuid($itemOrderInfo['uuid']))->first();

                $itemOrder->quantity = $itemOrderInfo['quantity'];
                $itemOrder->status   = ItemOrder::STATUS_AVAILABLE;

                if ($itemOrderInfo['quantity'] == 0) {
                    $itemOrder->status = ItemOrder::STATUS_NOT_AVAILABLE;
                }
                $itemOrder->save();
            }
        });

        $items = $order->itemOrders()
            ->with('item')
            ->scoped(new ByInitialRequest(false))
            ->scoped(new IsUserCustomItemOrSupply())
            ->paginate(1000);

        return BaseResource::collection($items);
    }
}
