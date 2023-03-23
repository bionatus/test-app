<?php

namespace App\Http\Controllers\Api\V4\Order\Active;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\Order\Scopes\ByActiveOrders;
use App\Models\Scopes\ByUser;
use App\Models\Scopes\NewestUpdated;
use App\Models\User;
use Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /** @var User $user */
        $orders = Order::scoped(new ByUser($user))->scoped(new ByActiveOrders())->scoped(new NewestUpdated())->with([
                'orderDelivery',
                'supplier',
                'user.pubnubChannels' => function($query) use ($user) {
                    $query->scoped(new ByUser($user));
                },
            ])->paginate();

        return BaseResource::collection($orders);
    }
}
