<?php

namespace App\Http\Controllers\LiveApi\V2\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\CustomItem\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsSupplierCustomItem;
use App\Models\Order;
use Auth;
use DB;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CustomItemController extends Controller
{
    public function index(Order $order)
    {
        $items = $order->itemOrders()->scoped(new IsSupplierCustomItem())->paginate();

        return BaseResource::collection($items);
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request, Order $order)
    {
        $itemOrder = DB::transaction(function() use ($request, $order) {
            $item = Item::create([
                'type' => Item::TYPE_CUSTOM_ITEM,
            ]);

            Auth::user()->supplier->customItems()->create([
                'id'   => $item->getKey(),
                'name' => $request->get(RequestKeys::NAME),
            ]);

            return ItemOrder::create([
                'item_id'            => $item->getKey(),
                'order_id'           => $order->getKey(),
                'status'             => ItemOrder::STATUS_AVAILABLE,
                'quantity'           => $request->get(RequestKeys::QUANTITY),
                'quantity_requested' => $request->get(RequestKeys::QUANTITY),
            ]);
        });

        return (new BaseResource($itemOrder))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Order $order, ItemOrder $supplierCustomItemItemOrder)
    {
        $item = $supplierCustomItemItemOrder->item;
        $supplierCustomItemItemOrder->delete();

        if (!$item->itemOrders()->exists()) {
            $item->delete();
        }

        return Response::noContent();
    }
}
