<?php

namespace App\Http\Controllers\Api\V3\Account;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Cart\StoreRequest;
use App\Http\Resources\Api\V3\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Scopes\ByUuid;
use Auth;
use Response;

class CartController extends Controller
{
    public function show()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        return new BaseResource($cart);
    }

    public function store(StoreRequest $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $this->addItems($cart, $request->get(RequestKeys::ITEMS));

        return new BaseResource($cart);
    }

    private function addItems(Cart $cart, array $items)
    {
        foreach ($items as $itemInfo) {
            /** @var Item $item */
            $item = Item::scoped(new ByUuid($itemInfo['uuid']))->first();
            $cart->cartItems()->firstOrCreate([
                'item_id'  => $item->getKey(),
                'quantity' => $itemInfo['quantity'],
            ]);

            if ($item->isSupply()) {
                $item->orderable->cartSupplyCounters()->create(['user_id' => Auth::id()]);
            }
        }
    }

    public function delete()
    {
        $user = Auth::user();
        if ($user->cart) {
            $user->cart->delete();
        }

        return Response::noContent();
    }
}
