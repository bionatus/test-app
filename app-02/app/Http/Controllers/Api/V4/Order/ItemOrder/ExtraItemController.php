<?php

namespace App\Http\Controllers\Api\V4\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\ItemOrder\ExtraItem\StoreRequest;
use App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\ItemOrder\Scopes\IsUserCustomItemOrSupply;
use App\Models\Order;
use App\Models\Scopes\ByUuid;
use Auth;
use DB;
use Symfony\Component\HttpFoundation\Response;

class ExtraItemController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request, Order $order)
    {
        DB::transaction(function() use ($request, $order) {
            foreach ($request->get(RequestKeys::ITEMS) as $itemInfo) {
                /** @var Item $item */
                $item = Item::scoped(new ByUuid($itemInfo['uuid']))->first();

                $order->itemOrders()->create([
                    'item_id'            => $item->getKey(),
                    'quantity'           => $itemInfo['quantity'],
                    'quantity_requested' => $itemInfo['quantity'],
                    'status'             => ItemOrder::STATUS_PENDING,
                    'initial_request'    => false,
                ]);

                if ($item->isSupply()) {
                    $item->orderable->cartSupplyCounters()->create(['user_id' => Auth::id()]);
                }
            }
        });

        $items = $order->itemOrders()
            ->with('item')
            ->scoped(new ByInitialRequest(false))
            ->scoped(new IsUserCustomItemOrSupply())
            ->paginate(1000);

        return BaseResource::collection($items)->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
