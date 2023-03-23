<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\CustomItem\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use Auth;
use DB;
use Throwable;

class CustomItemController extends Controller
{
    private ItemOrder $itemOrder;

    /**
     * @throws Throwable
     */
    public function store(StoreRequest $request, Order $order)
    {
        DB::transaction(function() use ($request, $order) {
            $item = Item::create([
                'type' => Item::TYPE_CUSTOM_ITEM,
            ]);

            Auth::user()->supplier->customItems()->create([
                'id'   => $item->getKey(),
                'name' => $request->get(RequestKeys::NAME),
            ]);

            $this->itemOrder = ItemOrder::create([
                'item_id'            => $item->getKey(),
                'order_id'           => $order->getKey(),
                'status'             => ItemOrder::STATUS_PENDING,
                'quantity'           => 0,
                'quantity_requested' => 0,
            ]);
        });

        return new BaseResource($this->itemOrder);
    }
}
