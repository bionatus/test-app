<?php

namespace App\Http\Controllers\LiveApi\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\User\Order\BaseResource;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Scopes\Newest;
use App\Models\Substatus;
use App\Models\User;
use Auth;

class OrderController extends Controller
{
    public function index(User $user)
    {
        $supplier = Auth::user()->supplier;

        $query = $user->orders()->with([
            'itemOrders',
            'orderDelivery',
            'user',
        ])->scoped(new BySupplier($supplier))->scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_PENDING,
                Substatus::STATUSES_PENDING_APPROVAL)))->scoped(new Newest());

        $page = $query->paginate(10);

        return BaseResource::collection($page);
    }
}
