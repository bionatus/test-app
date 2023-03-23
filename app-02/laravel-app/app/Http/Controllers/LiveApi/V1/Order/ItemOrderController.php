<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\ItemOrder\UpdateRequest;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Replacement;
use App\Models\Scopes\ByRouteKey;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ItemOrderController extends Controller
{
    public function index(Order $order)
    {
        $items = $order->itemOrders()->with([
            'item.orderable.item',
            'replacement.note',
            'replacement.singleReplacement.part.note',
            'replacement.singleReplacement.part.item',
        ])->paginate(1000);

        return BaseResource::collection($items);
    }

    public function show(Order $order, ItemOrder $itemOrder)
    {
        $itemOrder->load('item');

        return new BaseResource($itemOrder);
    }

    public function update(UpdateRequest $request, Order $order, ItemOrder $itemOrder)
    {
        $item                   = $itemOrder->item;
        $quantity               = $request->get(RequestKeys::QUANTITY, $itemOrder->quantity);
        $price                  = $request->get(RequestKeys::PRICE);
        $status                 = $request->get(RequestKeys::STATUS);
        $replacement            = $request->get(RequestKeys::REPLACEMENT);
        $supplyDetail           = $request->get(RequestKeys::SUPPLY_DETAIL);
        $customDetail           = $request->get(RequestKeys::CUSTOM_DETAIL);
        $replacementId          = null;
        $replacementDescription = null;
        if ($status === ItemOrder::STATUS_PENDING && $itemOrder->isPending()) {
            $status = ItemOrder::STATUS_AVAILABLE;
        }

        $update = [
            'quantity' => $quantity,
            'price'    => $price,
            'status'   => $status,
        ];

        if ($item->type === Item::TYPE_PART && !empty($replacement)) {
            if ($replacement['type'] == 'replacement') {
                $replacement   = Replacement::scoped(new ByRouteKey($replacement['id']))->first();
                $replacementId = $replacement->getKey();
            }

            if ($replacement['type'] == 'generic') {
                $replacementDescription = $replacement['description'];
            }
        }

        $update['supply_detail']            = $supplyDetail;
        $update['custom_detail']            = $customDetail;
        $update['generic_part_description'] = $replacementDescription;
        $update['replacement_id']           = $replacementId;

        $itemOrder->update($update);

        return (new BaseResource($itemOrder))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Order $order, ItemOrder $itemOrder)
    {
        $item = $itemOrder->item;
        $itemOrder->delete();

        if (!$item->itemOrders()->exists()) {
            $item->delete();
        }

        return Response::noContent();
    }
}
