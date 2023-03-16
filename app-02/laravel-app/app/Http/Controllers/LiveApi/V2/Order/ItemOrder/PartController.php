<?php

namespace App\Http\Controllers\LiveApi\V2\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\Part\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\Part\BaseResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Replacement;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByType;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PartController extends Controller
{
    public function index(Order $order)
    {
        $items = $order->itemOrders()->whereHas('item', function(Builder $subQuery) {
            $subQuery->scoped(new ByType(Item::TYPE_PART));
        })->paginate(1000);

        return BaseResource::collection($items);
    }

    public function update(UpdateRequest $request, Order $order, ItemOrder $partItemOrder)
    {
        $replacement = $request->get(RequestKeys::REPLACEMENT);

        $update = ['status' => $request->get(RequestKeys::STATUS)];

        $replacementId          = null;
        $replacementDescription = null;

        if (!empty($replacement) && $replacement['type'] == ItemOrder::REPLACEMENT_TYPE_GENERIC) {
            $replacementDescription = $replacement['description'];
        }

        if (!empty($replacement) && $replacement['type'] == ItemOrder::REPLACEMENT_TYPE_REPLACEMENT) {
            $replacement   = Replacement::scoped(new ByRouteKey($replacement['id']))->first();
            $replacementId = $replacement->getKey();
        }

        $update['generic_part_description'] = $replacementDescription;
        $update['replacement_id']           = $replacementId;

        $partItemOrder->update($update);

        return (new BaseResource($partItemOrder))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function show(Order $order, ItemOrder $partItemOrder)
    {
        $partItemOrder->load('item');

        return new BaseResource($partItemOrder);
    }
}
