<?php

namespace App\Http\Controllers\LiveApi\V2\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\Order\Assigned;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\Assignment\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\Scopes\ByRouteKey;
use App\Models\Staff;
use App\Models\Substatus;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class AssignController extends Controller
{
    public function store(StoreRequest $request, Order $order)
    {
        /** @var Staff $staff */
        $staff = Staff::scoped(new ByRouteKey($request->get(RequestKeys::STAFF)))->first();
        $order->orderStaffs()->create(['staff_id' => $staff->getKey()]);

        $order = App::make(ChangeStatus::class,
            ['order' => $order, 'substatusId' => Substatus::STATUS_PENDING_ASSIGNED])->execute();

        Assigned::dispatch($order);

        $pubnubChannel   = App::make(GetPubnubChannel::class, ['supplier' => $order->supplier, 'user' => $order->user])
            ->execute();
        $message         = PubnubMessageTypes::ORDER_ASSIGNED;
        $message['text'] = Lang::get($message['text'], ['staff' => $staff->name]);

        App::make(PublishMessage::class, [
            'pubnubChannel' => $pubnubChannel,
            'message'       => $message,
            'supplier'      => $order->supplier,
        ])->execute();

        return (new DetailedResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
