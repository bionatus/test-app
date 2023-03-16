<?php

namespace App\Http\Controllers\Api\V3\Account\Wishlist;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\StoreRequest;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist\BaseResource;
use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Scopes\ByRouteKey;
use App\Models\Wishlist;
use Response;

class ItemWishlistController extends Controller
{
    public function index(Wishlist $wishlist)
    {
        $items = $wishlist->itemWishlists()->paginate();

        return BaseResource::collection($items);
    }

    public function update(UpdateRequest $request, Wishlist $wishlist, ItemWishlist $itemWishlist)
    {
        $itemWishlist->quantity = $request->get(RequestKeys::QUANTITY);
        $itemWishlist->save();

        return new BaseResource($itemWishlist);
    }

    public function store(StoreRequest $request, Wishlist $wishlist)
    {
        $item = Item::scoped(new ByRouteKey($request->get(RequestKeys::ITEM)))->first();

        /** @var ItemWishlist $itemWishlist */
        $itemWishlist = $wishlist->itemWishlists()->create([
            'item_id'  => $item->getKey(),
            'quantity' => $request->get(RequestKeys::QUANTITY),
        ]);

        return new BaseResource($itemWishlist);
    }

    public function delete(Wishlist $wishlist, ItemWishlist $itemWishlist)
    {
        $itemWishlist->delete();

        return Response::noContent();
    }
}
