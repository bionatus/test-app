<?php

namespace App\Http\Controllers\LiveApi\V2\Supplier;

use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Supplier\User\IndexRequest;
use App\Http\Resources\LiveApi\V2\Supplier\User\BaseResource;
use App\Models\User;
use App\Models\User\Scopes\ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier;
use Auth;
use Illuminate\Database\Eloquent\Collection;

class UserController extends Controller
{
    public function index(IndexRequest $request)
    {
        $searchString = $request->get(RequestKeys::SEARCH_STRING);
        $supplier     = Auth::user()->supplier;
        $query        = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier,
            $searchString));
        $users        = $query->paginate();

        $users->map(function(User $user) use ($supplier) {
            $pubnubChannel = (new GetPubnubChannel($supplier, $user))->execute();
            $pubnubChannel->setAttribute('last_message_at', $user->last_message_at);
            $user->setRelation('pubnubChannels', Collection::make([
                $pubnubChannel,
            ]));

            return $user;
        });

        return BaseResource::collection($users);
    }
}
