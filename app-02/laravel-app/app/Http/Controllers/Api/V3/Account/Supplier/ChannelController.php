<?php

namespace App\Http\Controllers\Api\V3\Account\Supplier;

use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Supplier\Channel\BaseResource;
use App\Models\Scopes\ByUser;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOrdersAndSupplierUsersWithoutOrdersInUser;
use Auth;
use Illuminate\Database\Eloquent\Collection;

class ChannelController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $suppliers = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->with([
            'orders'         => function($query) use ($user) {
                $query->orderByDesc('updated_at');
            },
            'pubnubChannels' => function($query) use ($user) {
                $query->scoped(new ByUser($user))->limit(1);
            },
        ])->paginate();

        $suppliers->map(function(Supplier $supplier) use ($user) {
            $pubnubChannel = (new GetPubnubChannel($supplier, $user))->execute();
            $supplier->setRelation('pubnubChannels', Collection::make([
                $pubnubChannel,
            ]));

            return $supplier;
        });

        return BaseResource::collection($suppliers);
    }
}
