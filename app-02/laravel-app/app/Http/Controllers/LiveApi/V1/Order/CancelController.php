<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\Declined;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class CancelController extends Controller
{
    public function __invoke(Order $order)
    {
        $order = App::make(ChangeStatus::class,
            ['order' => $order, 'substatusId' => Substatus::STATUS_CANCELED_DECLINED])->execute();

        Declined::dispatch($order);

        $pubnubChannel = (new GetPubnubChannel($order->supplier, $order->user))->execute();
        $message       = PubnubMessageTypes::ORDER_DECLINED;

        App::make(PublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $order->supplier,
        ])->execute();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
