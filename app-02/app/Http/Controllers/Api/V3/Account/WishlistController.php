<?php

namespace App\Http\Controllers\Api\V3\Account;

use App;
use App\Actions\Models\Wishlist\MakeNameUnique;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Wishlist\StoreRequest;
use App\Http\Requests\Api\V3\Account\Wishlist\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Wishlist\BaseResource;
use App\Models\Wishlist;
use App\Models\Wishlist\Scopes\ByUser;
use App\Models\Wishlist\Scopes\Newest;
use Auth;
use Response;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::scoped(new ByUser(Auth::user()))->scoped(new Newest())->paginate();

        return BaseResource::collection($wishlists);
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request)
    {
        $user = Auth::user();

        $uniqueNameAction = App::make(MakeNameUnique::class);
        $name             = $uniqueNameAction->execute($user, $request->get(RequestKeys::NAME));
        $wishlist         = Wishlist::create([
            'user_id' => $user->getKey(),
            'name'    => $name,
        ]);

        return new BaseResource($wishlist);
    }

    public function update(UpdateRequest $request, Wishlist $wishlist)
    {
        $user             = Auth::user();
        $uniqueNameAction = App::make(MakeNameUnique::class);
        $newName          = $request->get(RequestKeys::NAME);

        if ($wishlist->name !== $newName) {
            $name = $uniqueNameAction->execute($user, $newName);
            $wishlist->update(['name' => $name]);
        }

        return new BaseResource($wishlist);
    }
    
    public function delete(Wishlist $wishlist)
    {
        $wishlist->delete();

        return Response::noContent();
    }
}
