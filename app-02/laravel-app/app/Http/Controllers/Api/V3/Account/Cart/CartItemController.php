<?php

namespace App\Http\Controllers\Api\V3\Account\Cart;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\StoreRequest;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Cart\CartItem\BaseResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Scopes\ByRouteKey;
use Auth;
use Response;

class CartItemController extends Controller
{
    public function index()
    {
        $cart      = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cartItems = $cart->cartItems()->with('item')->paginate();

        return BaseResource::collection($cartItems);
    }

    public function update(UpdateRequest $request, CartItem $cartItem)
    {
        $cartItem->quantity = $request->get(RequestKeys::QUANTITY);
        $cartItem->save();

        return new BaseResource($cartItem);
    }

    public function store(StoreRequest $request)
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        /** @var Item $item */
        $item = Item::scoped(new ByRouteKey($request->get(RequestKeys::ITEM)))->first();

        /** @var CartItem $cartItem */
        $cartItem = $cart->cartItems()->updateOrCreate([
            'item_id'  => $item->getKey(),
            'quantity' => $request->get(RequestKeys::QUANTITY),
        ]);
        if ($item->isSupply()) {
            $item->orderable->cartSupplyCounters()->create(['user_id' => $user->getRouteKey()]);
        }

        return new BaseResource($cartItem);
    }

    public function delete(CartItem $cartItem)
    {
        $cartItem->delete();

        return Response::noContent();
    }
}
