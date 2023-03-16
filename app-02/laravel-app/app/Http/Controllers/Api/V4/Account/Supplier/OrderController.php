<?php

namespace App\Http\Controllers\Api\V4\Account\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Account\Supplier\Order\BaseResource;
use App\Models\Order\Scopes\OrderedByGroupedStatus;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\Latest;
use App\Models\Supplier;
use Auth;

class OrderController extends Controller
{
    public function index(Supplier $supplier)
    {
        $user = Auth::user();

        $orders = $user->orders()
            ->scoped(new BySupplier($supplier))
            ->scoped(new OrderedByGroupedStatus())
            ->scoped(new Latest())
            ->with([
                'orderDelivery.deliverable.destinationAddress',
                'lastOrderStaff.staff',
            ])
            ->paginate();

        return BaseResource::collection($orders);
    }
}
