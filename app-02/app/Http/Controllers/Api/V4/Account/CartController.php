<?php

namespace App\Http\Controllers\Api\V4\Account;

use App;
use App\Actions\Models\Cart\GetCart;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Account\Cart\StoreRequest;
use App\Http\Requests\Api\V4\Account\Cart\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByUuid;
use App\Models\Supplier;
use App\Models\User;
use Auth;
use Response;

class CartController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();

        $cart = App::make(GetCart::class, ['user' => $user])->execute();

        return new BaseResource($cart);
    }

    public function store(StoreRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $cart = App::make(GetCart::class, ['user' => $user])->execute();

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

    public function update(UpdateRequest $request)
    {
        $user              = Auth::user();
        $cart              = Cart::firstOrCreate(['user_id' => $user->getKey()]);
        $supplier          = Supplier::scoped(new ByRouteKey($request->get(RequestKeys::SUPPLIER)))->first();
        $cart->supplier_id = $supplier->getKey();
        $cart->save();

        return new BaseResource($cart);
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
