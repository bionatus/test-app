<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\Order\Assigned;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\Assignment\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class AssignController extends Controller
{
    public function store(StoreRequest $request, Order $order)
    {
        $order->working_on_it = $request->get(RequestKeys::NAME);
        $order->save();

        Assigned::dispatch($order);

        $pubnubChannel   = App::make(GetPubnubChannel::class, ['supplier' => $order->supplier, 'user' => $order->user])
            ->execute();
        $message         = PubnubMessageTypes::ORDER_ASSIGNED;
        $message['text'] = Lang::get($message['text'], ['staff' => $order->working_on_it]);

        App::make(PublishMessage::class, [
            'pubnubChannel' => $pubnubChannel,
            'message'       => $message,
            'supplier'      => $order->supplier,
        ])->execute();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function delete(Order $order)
    {
        $order->working_on_it = null;
        $order->save();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_OK);
    }
}
