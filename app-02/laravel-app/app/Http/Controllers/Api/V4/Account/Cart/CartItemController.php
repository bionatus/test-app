<?php

namespace App\Http\Controllers\Api\V4\Account\Cart;

use App;
use App\Actions\Models\Cart\GetCart;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Account\Cart\CartItem\StoreRequest;
use App\Http\Requests\Api\V4\Account\Cart\CartItem\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Cart\CartItem\BaseResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Scopes\ByRouteKey;
use Arr;
use Auth;
use Illuminate\Support\Collection;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
        $user   = Auth::user();
        $userId = $user->getKey();

        $cart = App::make(GetCart::class, ['user' => $user])->execute();

        $items     = Collection::make($request->get(RequestKeys::ITEMS));
        $cartItems = $cart->cartItems;

        $items->each(function(array $item) use ($cart, $cartItems, $userId) {
            /** @var Item $itemModel */
            $itemModel = Item::scoped(new ByRouteKey(Arr::get($item, 'uuid')))->first();

            $cartItem = $cartItems->firstWhere('item_id', $itemModel->getKey());
            if ($cartItem) {
                $cartItem->quantity += Arr::get($item, 'quantity');
                $cartItem->save();
            } else {
                $cart->cartItems()->create([
                    'item_id'  => $itemModel->getKey(),
                    'quantity' => Arr::get($item, 'quantity'),
                ]);
            }

            if ($itemModel->isSupply()) {
                $itemModel->orderable->cartSupplyCounters()->create(['user_id' => $userId]);
            }
        });

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }

    public function delete(CartItem $cartItem)
    {
        $cartItem->delete();

        return Response::noContent();
    }
}
